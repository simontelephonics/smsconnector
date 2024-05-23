<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Bulkvs extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Bulk Solutions');
        $this->nameRaw    = 'bulkvs';
        $this->APIUrlInfo = 'https://portal.bulkvs.com/api/v1.0/documentation';
        $this->APIVersion = 'v1.0';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string',
                'label'       => _('API Username'),
                'help'        => _("Enter the Bulkvs API Username"),
                'default'     => '',
                'required'    => true,
                'placeholder' => _('Enter Key'),
            ),
            'api_secret' => array(
                'type'        => 'string',
                'label'       => _('API Password/Token'),
                'help'        => _("Enter the Bulkvs API Password/Token"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Key'),
            ),
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $req = array(
            'From'       => '+'.$from,
            'To'         => array('+'.$to),
            'MediaURLs'  => $this->media_urls($id)
        );
        if ($message)
        {
            $req['Message'] = $message;
        }
        $this->sendBulkvs($req, $id);
        return true;
    }
    
    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'From'     => '+'.$from,
            'To'       => array('+'.$to),
            'Message'  => $message
        );
        $this->sendBulkvs($req, $id);
        return true;
    }

    private function sendBulkvs($payload, $mid): void
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            'auth' => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        $headers = array("Content-Type" => "application/json");
        $url     = sprintf('https://portal.bulkvs.com/api/%s/messageSend', $this->APIVersion);
        $json    = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $bulkvsResponse = $session->post('', $headers, $json, $options);
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $bulkvsResponse->status_code, $bulkvsResponse->body));
            if (! $bulkvsResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $bulkvsResponse->status_code, $bulkvsResponse->body));
            }
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception(sprintf(_('Unable to send message: %s'), $e->getMessage()));
        }
    }

    public function callPublic($connector)
    {
        $return_code = 200;
        if ($_SERVER['REQUEST_METHOD'] === "POST") 
        {
            $postdata = file_get_contents("php://input");
            $sms      = json_decode($postdata);

            freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s) in: %s"), $this->nameRaw, print_r($postdata, true)));
            if (empty($sms)) 
            { 
                $return_code = 403;
            }
            elseif (! empty($sms->DeliveryReceipt)) 
            {
                // not handling DLR for now
                $return_code = 200;
            }
            else
            {
                $from = ltrim($sms->From, '+'); // strip +
                $to   = ltrim($sms->To[0], '+'); // strip +
                $text = urldecode($sms->Message);
                $emid = null;
                $images = array();

                if (isset($sms->MediaURLs)) 
                {
                    foreach ($sms->MediaURLs as $media) 
                    {
                        $data  = file_get_contents($media);
                        $purl = parse_url($media);
                        $path_parts = pathinfo($purl['path']);
                        $ext = $path_parts['extension'];
                        
                        switch ($ext)
                        {
                            case 'txt':
                                $text .= $data;
                                break;
                            case 'smil':
                                break;
                            default:
                                $name = basename($purl['path']);
                                $images[$name] = $data;
                                break;
                        }
                    }
                }

                try 
                {
                    $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                } 
                catch (\Exception $e) 
                {
                    throw new \Exception(sprintf(_('Unable to get message: %s'), $e->getMessage()));
                }
        
                if (! empty($images))
                {
                    foreach ($images as $name => $img)
                    {
                        $name = $msgid . $name;
                        try 
                        {
                            $connector->addMedia($msgid, $name, $img);
                        } 
                        catch (\Exception $e) 
                        {
                            throw new \Exception(sprintf(_('Unable to store MMS media: %s'), $e->getMessage()));
                        }
                    }    
                }

                $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
                $return_code = 200;
            }
        }
        else
        {
            $return_code = 405;
        }
        return $return_code;
    }
}