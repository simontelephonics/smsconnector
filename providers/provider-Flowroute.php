<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Flowroute extends providerBase {

    public function __construct ()
    {
        parent::__construct();
        $this->name     = _('Flowroute');
        $this->nameRaw  = 'flowroute';

        $this->configInfo = array(
            'api_key' => array(
                'type'    => 'string',
                'label'   => _('API Key'),
                'help'    => _("Enter the Flowroute API key"),
                'default' => ''
            ),
            'api_secret' => array(
                'type'    => 'string',
                'label'   => _('API Secret'),
                'help'    => _("Enter the Flowroute API secret"),
                'default' => ''
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $retval = parent::sendMedia($id, $to, $from, $message);

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
        $req = json_encode(
            array(
                "data" => array(
                    "type"       => "message",
                    "attributes" => $attr
                )
            )
        );
        $this->sendFlowroute($req, $id);
        return $retval;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $retval = parent::sendMessage($id, $to, $from, $message);

        $req = json_encode(
            array(
                "data" => array(
                    "type" => "message",
                    "attributes" => array(
                        "to"    => '+'.$to,
                        "from"  => '+'.$from,
                        "body"  => $message
                    )
                )
            )
        );
        $this->sendFlowroute($req, $id);
        return $retval;
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
        $url = 'https://api.flowroute.com/v2.2/messages';
        $session = \FreePBX::Curl()->requests($url);
        try {
            $flowrouteResponse = $session->post('', $headers, $payload, $options);
            freepbx_log(FPBX_LOG_INFO, $flowrouteResponse->body, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }

}