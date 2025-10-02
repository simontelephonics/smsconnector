<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Siptrunk extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Siptrunk');
        $this->nameRaw    = 'siptrunk';
        $this->APIUrlInfo = 'https://support.siptrunk.com/';
        $this->APIVersion = 'v1.0';

        $this->configInfo = array(
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('API Key'),
                'help'      => _("Enter the Siptrunk API access key"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Key'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Secret'),
                'help'      => _("Enter the Siptrunk API secret key"),
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
            "body"       => '',
            "is_mms"     => 'true',
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
        $this->sendSiptrunk($req, $id);
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
        $this->sendSiptrunk($req, $id);
        return true;
    }

    private function sendSiptrunk($payload, $mid): void
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            "auth" => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        $headers = array("Content-Type" => "application/vnd.api+json");
        $url = 'https://messaging.siptrunk.com';
        $json = json_encode($payload);
        $session = \FreePBX::Curl()->requests($url);
        try
        {
            $siptrunkResponse = $session->post('', $headers, $json, $options);
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $siptrunkResponse->status_code, $siptrunkResponse->body));
            if (! $siptrunkResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $siptrunkResponse->status_code, $siptrunkResponse->body));
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
                    if (isset($sms->data->type) && ($sms->data->type == 'message'))
                    {
                        $from = ltrim($sms->data->attributes->from, '+'); // strip + if exists
                        $to   = ltrim($sms->data->attributes->to, '+'); // strip + if exists
                        $text = $sms->data->attributes->body ?? '';
                        $emid = $sms->data->message_id;

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
                                $img = file_get_contents($media->attributes->url);
                                if ($img)
                                {
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