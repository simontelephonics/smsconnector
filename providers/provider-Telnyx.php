<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Telnyx extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Telnyx');
        $this->nameRaw    = 'telnyx';
        $this->APIUrlInfo = 'https://developers.telnyx.com/docs/api/v2/messaging';
        $this->APIVersion = 'v2';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string',
                'label'       => _('API Key'),
                'help'        => _("Enter the Telnyx v2 API key"),
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
            'from'       => '+'.$from,
            'to'         => (strlen($to) >= 5 && strlen($to) <= 6) ? $to : '+'.$to, // allows short codes, otherwise E.164
            'media_urls' => $this->media_urls($id)
        );
        if ($message)
        {
            $req['text'] = $message;
        }
        $this->sendTelnyx($req, $id);
        return true;
    }
    
    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'from'  => '+'.$from,
            'to'    => (strlen($to) >= 5 && strlen($to) <= 6) ? $to : '+'.$to, // allows short codes, otherwise E.164
            'text'  => $message
        );
        $this->sendTelnyx($req, $id);
        return true;
    }

    private function sendTelnyx($payload, $mid): void
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Authorization" => sprintf("Bearer %s", $config['api_key']),
            "Content-Type" => "application/json"
        );
        $url = sprintf('https://api.telnyx.com/%s/messages', $this->APIVersion);
        $json = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $telnyxResponse = $session->post('', $headers, $json, array());
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $telnyxResponse->status_code, $telnyxResponse->body));
            if (! $telnyxResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $telnyxResponse->status_code, $telnyxResponse->body));
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

            freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s) in: %s"), $this->nameRaw, print_r($postdata, true)));
            if (empty($sms)) 
            { 
                $return_code = 403;
            }
            else
            {
                if (isset($sms->data)) 
                {
                    if (isset($sms->data->event_type) && ($sms->data->event_type == 'message.received')) 
                    {
                        $from = ltrim($sms->data->payload->from->phone_number, '+'); // strip +
                        $to   = ltrim($sms->data->payload->to[0]->phone_number, '+'); // strip +
                        $text = $sms->data->payload->text ?? '';
                        $emid = $sms->data->id;

                        try 
                        {
                            $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                        } 
                        catch (\Exception $e) 
                        {
                            throw new \Exception(sprintf(_('Unable to get message: %s'), $e->getMessage()));
                        }
                
                        if (isset($sms->data->payload->media[0])) 
                        {
                            foreach ($sms->data->payload->media as $media) 
                            {
                                $img  = file_get_contents($media->url);
                                $purl = parse_url($media->url);
                                $name = $msgid . basename($purl['path']);
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