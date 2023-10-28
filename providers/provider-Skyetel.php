<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Skyetel extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Skyetel');
        $this->nameRaw    = 'skyetel';
        $this->APIUrlInfo = 'https://support.skyetel.com/hc/en-us/articles/360056299914-SMS-MMS-API';
        $this->APIVersion = 'v1';

        $this->configInfo = array(
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('API Key'),
                'help'      => _("Enter the Skyetel API key"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Key'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Secret'),
                'help'      => _("Enter the Skyetel API secret"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter Secret'),
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message='')
    {
        $req = array(
            'to'         => $to,
            'text'       => $message,
            'media' => $this->media_urls($id)
        );
        $this->sendSkyetel($req, $from, $id);
        return true;
    }
    
    public function sendMessage($id, $to, $from, $message='')
    {
        $req = array(
            'to'    => $to,
            'text'  => $message
        );
        $this->sendSkyetel($req, $from, $id);
        return true;
    }

    private function sendSkyetel($payload, $from, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            "auth" => array(
                $config['api_key'],
                $config['api_secret']
            )
        );

        $headers = array("Content-Type" => "application/json");
        $url = 'https://sms.skyetel.com/v1/out?from=' . $from; // "from" comes from FreePBX as 11 digit number, the correct format
        $json = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $skyetelResponse = $session->post('', $headers, $json, $options);
            freepbx_log(FPBX_LOG_INFO, sprintf("%s responds: HTTP %s, %s", $this->nameRaw, $skyetelResponse->status_code, $skyetelResponse->body), true);
            if (! $skyetelResponse->success)
            {
                throw new \Exception("HTTP $skyetelResponse->status_code, $skyetelResponse->body");
            }
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }

    public function callPublic($connector)
    {
        $return_code = 202;
        if ($_SERVER['REQUEST_METHOD'] === "POST") 
        {
            $postdata = file_get_contents("php://input");
            $sms      = json_decode($postdata);

            freepbx_log(FPBX_LOG_INFO, sprintf("Webhook (%s) in: %s", $this->nameRaw, print_r($postdata, true)));
            if (empty($sms)) 
            { 
                $return_code = 403;
            }
            else
            {
                $from = ltrim($sms->from, '+'); // strip + if exists
                $to   = ltrim($sms->to, '+'); // strip + if exists
                $text = $sms->text;
                $emid = null;

                try 
                {
                    $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                } 
                catch (\Exception $e) 
                {
                    throw new \Exception(sprintf('Unable to get message: %s', $e->getMessage()));
                }
                
                if (isset($sms->media[0])) 
                {
                    foreach ($sms->media as $media) 
                    {
                        $img  = file_get_contents($media);
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
                }
                $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
            }
        }
        $return_code = 202;
        return $return_code;
    }
}