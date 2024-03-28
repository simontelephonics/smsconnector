<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Flowroute extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Flowroute');
        $this->nameRaw    = 'flowroute';
        $this->APIUrlInfo = 'https://developer.flowroute.com/api/messages/v2.2/';
        $this->APIVersion = 'v2.2';

        $this->configInfo = array(
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('API Key'),
                'help'      => _("Enter the Flowroute API key"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Key'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Secret'),
                'help'      => _("Enter the Flowroute API secret"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter Secret'),
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $attr = array(
            "to"         => '+'.$to,
            "from"       => '+'.$from,
            "is_mms"     => "true",
            "media_urls" => $this->media_urls($id)
        );
        if ($message)
        {
            $attr['body'] = $message;
        }
        $req = array(
            "data" => array(
                "type"       => "message",
                "attributes" => $attr
            )
        );
        $this->sendFlowroute($req, $id);
        return true;
    }
    
    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            "data" => array(
                "type" => "message",
                "attributes" => array(
                    "to"    => '+'.$to,
                    "from"  => '+'.$from,
                    "body"  => $message
                )
            )
        );
        $this->sendFlowroute($req, $id);
        return true;
    }

    private function sendFlowroute($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            "auth" => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        $headers = array("Content-Type" => "application/vnd.api+json");
        $url     = sprintf('https://api.flowroute.com/%s/messages', $this->APIVersion);
        $json    = json_encode($payload);
        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $flowrouteResponse = $session->post('', $headers, $json, $options);
            $this->LogInfo(sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $flowrouteResponse->status_code, $flowrouteResponse->body));
            if (! $flowrouteResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $flowrouteResponse->status_code, $flowrouteResponse->body));
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
        $return_code = 202;

        if ($_SERVER['REQUEST_METHOD'] === "POST") 
        {
            $postdata = file_get_contents("php://input");
            $sms      = json_decode($postdata);

            $this->LogInfo(sprintf(_("Webhook (%s) in: %s"), $this->nameRaw, print_r($postdata, true)));
            if (empty($sms)) 
            { 
                $return_code = 403;
            }
            else
            {
                if (isset($sms->data)) 
                {
                    if (isset($sms->data->type) && ($sms->data->type == 'message')) 
                    {
                        $from = ltrim($sms->data->attributes->from, '+'); // strip + if exists
                        $to   = ltrim($sms->data->attributes->to, '+'); // strip + if exists
                        $text = $sms->data->attributes->body;
                        $emid = $sms->data->id;
                        
                        try 
                        {
                            $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                        } 
                        catch (\Exception $e) 
                        {
                            throw new \Exception(sprintf(_('Unable to get message: %s'), $e->getMessage()));
                        }
                
                        if (isset($sms->included[0])) 
                        {
                            foreach ($sms->included as $media) 
                            {
                                if ($media->type == 'media') 
                                {
                                    $img = file_get_contents($media->attributes->url);
                                    $name = $msgid . $media->attributes->file_name;
                                
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
                        }
                        
                        $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
                    }
                }
                $return_code = 202;
            }
        }
        else
        {
            $return_code = 405;
        }
        return $return_code;
    }
}