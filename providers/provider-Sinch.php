<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Sinch extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Sinch');
        $this->nameRaw    = 'sinch';
        $this->APIUrlInfo = 'https://developers.sinch.com/docs/sms/api-reference/';
        $this->APIVersion = 'v1';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string',
                'label'       => _('Service Plan ID'),
                'help'        => _("Enter the Sinch SMS Service Plan ID"),
                'default'     => '',
                'required'    => true,
                'placeholder' => _('Enter Service Plan ID'),
            ),
            'api_secret' => array(
                'type'        => 'string',
                'label'       => _('Enter API token'),
                'help'        => _('Enter the Sinch Service API token'),
                'default'     => '',
                'required'    => true,
                'placeholder' => _('Enter API token'),
            )
        );
    }

    public function sendMedia($id, $to, $from, $message=null)
    {
        $req = array(
            'from'       => $from,
            'to'         => array($to),
            'body'       => (object)[
                'url'    => $this->media_urls($id)[0]
            ],
            'type'       => 'mt_media',
        );
        if ($message)
        {
            $req['body']['message'] = $message;
        }
        $this->sendSinch($req, $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'from'  => $from,
            'to'    => array($to),
            'body'  => $message
        );
        $this->sendSinch($req, $id);
        return true;
    }

    private function sendSinch($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Authorization" => sprintf("Bearer %s", $config['api_secret']),
            "Content-Type" => "application/json"
        );
        $url = sprintf('https://sms.api.sinch.com/xms/%s/%s/batches', $this->APIVersion, $config['api_key']);
        $json = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try
        {
            $sinchResponse = $session->post('', $headers, $json, array());
            $this->LogInfo(sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $sinchResponse->status_code, $sinchResponse->body));

            if (! $sinchResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $sinchResponse->status_code, $sinchResponse->body));
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
                if (isset($sms->type) && ($sms->type == 'mo_text'))
                {
                    $from = $sms->from;
                    $to   = $sms->to;
                    $text = $sms->body;
                    $emid = $sms->id;

                    try
                    {
                        $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                    }
                    catch (\Exception $e)
                    {
                        throw new \Exception(sprintf(_('Unable to get message: %s'), $e->getMessage()));
                    }

                    if (isset($sms->body->url))
                    {
                        $img  = file_get_contents($sms->body->url);
                        $purl = parse_url($sms->body->url);
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
                    $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
                }
                else if (isset($sms->type))
                {
                    // don't know what to do yet; log it
                    $this->LogInfo(print_r($sms, true));
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