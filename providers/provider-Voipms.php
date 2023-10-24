<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Voipms extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Voip.ms');
        $this->nameRaw  = 'voipms';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string', 
                'label'       => _('Username'),
                'help'        => _("The e-mail address you use to log in to Voip.ms"),
                'default'     => '',
                'class'       => '',
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
        // we have to send media items as base64-encoded form fields
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
            'method' => 'sendSMS'
        );
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
        $qs = array(
            'api_username' => $config['api_key'],
            'api_password' => $config['api_secret'],
            'method'       => $payload['method'],
            'did'          => $from,
            'dst'          => $payload['to'],
        );
        if (! empty($payload['text'])) 
        {
            $qs['message'] = $payload['text'];
        }
        if (! empty($payload['media']))
        {
            $counter = 1;
            foreach ($payload['media'] as $media_item)
            {
                $qs['media'.$counter] = $media_item;
                $counter++;
            }
        }

        // build a multipart/form-data request
        $crlf = "\r\n";
        $mimeBoundary = md5(time());
        $reqbody = '';
        
        foreach ($qs as $key => $value)
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
            freepbx_log(FPBX_LOG_INFO, $voipmsResponse->body, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' . $e->getMessage());
        }
    }

    public function callPublic($connector)
    {
        $return_code = 202;
        if ($_SERVER['REQUEST_METHOD'] === "GET") 
        {
            if (strstr($_SERVER['QUERY_STRING'], ';') !== FALSE) // using ; as separator
            {
                $qs = str_replace(';', '&', $_SERVER['QUERY_STRING']);
                print_r($qs);
                parse_str($qs, $sms);
                print_r($sms);
                print($sms['provider']);
            } else {
                $sms = $_GET;
            }
            $to ='1'.$sms['to'];
            $from = '1'.$sms['from'];
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
                $name = basename($purl['path']);
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
        }
        return $return_code;
    }

    public function getWebHookUrl()
    {
        $ampWebAddress = $this->getWebAddress();
        return sprintf("https://%s/smsconn/provider.php?provider=%s;to={TO};from={FROM};message={MESSAGE};id={ID};date={TIMESTAMP};media={MEDIA}", $ampWebAddress, $this->nameRaw);
    }
}