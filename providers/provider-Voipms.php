<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Voipms extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Voip.ms');
        $this->nameRaw  = 'voipms';
        $this->APIUrlInfo = 'https://voip.ms/m/apidocs.php';
        $this->APIVersion = 'v1';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string', 
                'label'       => _('Username'),
                'help'        => _("The e-mail address you use to log in to Voip.ms"),
                'default'     => '',
                'required'    => true, // True to set this property as required to make the provider available.
                'placeholder' => _('Voip.ms e-mail address'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Secret'),
                'help'      => _("Enter the Voip.ms API password"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter Password'),
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message='')
    {
        // We have to send media items as base64-encoded form fields
        // However, Voip.ms does not require any metadata on the media such as 
        // content type, so it is enough to just grab the raw and encode it
        $base64media = array();
        $sql = 'SELECT raw FROM sms_media WHERE mid = :mid';
        $stmt = $this->Database->prepare($sql);
        $stmt->bindParam(':mid', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $media = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($media as $media_item)
        {
            $base64media[] = base64_encode($media_item['raw']);
        }

        $req = array(
            'to'         => $to,
            'text'       => $message,
            'method'     => 'sendMMS',
            'media'      => $base64media
        );
        $this->sendVoipms($req, $from, $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message='')
    {
        $req = array(
            'to'     => $to,
            'text'   => $message,
        );
        if (strlen($message) <= 160)
        {
            $req['method'] = 'sendSMS';
        }
        else
        {
            $req['method'] = 'sendMMS';
        }
        $this->sendVoipms($req, $from, $id);
        return true;
    }

    private function sendVoipms($payload, $from, $mid)
    {
        if ((strlen($from) == 11) && (strpos($from, '1') === 0)) 
        {
            $from = ltrim($from, '1');
        }
        if ((strlen($payload['to']) == 11) && (strpos($payload['to'], '1') === 0))
        {
            $payload['to'] = ltrim($payload['to'], '1');
        }
        $config = $this->getConfig($this->nameRaw);
        $fields = array(
            'api_username' => $config['api_key'],
            'api_password' => $config['api_secret'],
            'method'       => $payload['method'],
            'did'          => $from,
            'dst'          => $payload['to'],
        );
        if (! empty($payload['text'])) 
        {
            $fields['message'] = $payload['text'];
        }
        if (! empty($payload['media']))
        {
            $counter = 1;
            foreach ($payload['media'] as $media_item)
            {
                $fields['media'.$counter] = $media_item;
                $counter++;
            }
        }

        // build a multipart/form-data request
        $crlf = "\r\n";
        $mimeBoundary = '----' . md5(time());
        $reqbody = '';
        
        foreach ($fields as $key => $value)
        {
            $reqbody .= '--' . $mimeBoundary . $crlf;
            $reqbody .= 'Content-Disposition: form-data; name="' . $key . '"' . $crlf . $crlf;
            $reqbody .= $value . $crlf;
        }
        $reqbody .= '--' . $mimeBoundary . '--' . $crlf . $crlf;
       
        $headers = array("Content-Type" => "multipart/form-data; boundary=$mimeBoundary");
        $url = 'https://voip.ms/api/v1/rest.php';

        $session = \FreePBX::Curl()->requests($url);
        try
        {
            $voipmsResponse = $session->post('', $headers, $reqbody, array());
            freepbx_log(FPBX_LOG_INFO, sprintf("%s responds: HTTP %s, %s", $this->nameRaw, $voipmsResponse->status_code, $voipmsResponse->body), true);
            if (! $voipmsResponse->success)
            {
                throw new \Exception("HTTP $voipmsResponse->status_code, $voipmsResponse->body");
            }
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' . $e->getMessage());
        }
    }

    public function callPublic($connector)
    {
        $return_code = 405;
        if ($_SERVER['REQUEST_METHOD'] === "GET") 
        {
            if (strstr($_SERVER['QUERY_STRING'], ';') !== FALSE) // using ; as separator
            {
                $qs = str_replace(';', '&', $_SERVER['QUERY_STRING']);
                parse_str($qs, $sms);
            } else {
                $sms = $_GET;
            }
            freepbx_log(FPBX_LOG_INFO, sprintf("Webhook (%s) in: %s", $this->nameRaw, print_r($sms, true)));

            $to = $sms['to'];
            if (preg_match('/^[2-9]\d{2}[2-9]\d{6}$/', $to)) // ten digit NANP
            {
                $to = '1'.$to;
            }
            
            $from = $sms['from'];
            if (preg_match('/^[2-9]\d{2}[2-9]\d{6}$/', $from)) // ten digit NANP
            {
                $from = '1'.$from;
            }

            $text = $sms['message'];
            $emid = $sms['id'];
            //$date = $sms['date'];
            $media = $sms['media'];

            try
            {
                $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
            }
            catch (\Exception $e)
            {
                throw new \Exception(sprintf('Unable to get message: %s', $e->getMessage()));
            }

            if ($media)
            {
                $img = file_get_contents($media);
                $purl = parse_url($media);
                $name = $msgid . basename($purl['path']);
                try
                {
                    $connector->addMedia($msgid, $name, $img);
                }
                catch (\Exception $e)
                {
                    throw new \Exception(sprintf('Unable to store MMS media: %s', $e->getMessage()));
                }
            }
            $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
            $return_code = 202;
        }
        return $return_code;
    }

    public function getWebHookUrl()
    {
        $ampWebAddress = $this->getWebAddress();
        return sprintf("https://%s/smsconn/provider.php?provider=%s;to={TO};from={FROM};message={MESSAGE};id={ID};date={TIMESTAMP};media={MEDIA}", $ampWebAddress, $this->nameRaw);
    }
}