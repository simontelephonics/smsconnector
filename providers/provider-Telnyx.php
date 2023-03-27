<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Telnyx extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Telnyx');
        $this->nameRaw  = 'telnyx';

        $this->configInfo = array(
            'api_key' => array(
                'type'    => 'string',
                'label'   => _('API Key'),
                'help'    => _("Enter the Telnyx v2 API key"),
                'default' => ''
            ),
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $req = array(
            'from'       => '+'.$from,
            'to'         => '+'.$to,
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
            'to'    => '+'.$to,
            'text'  => $message
        );
        $this->sendTelnyx($req, $id);
        return true;
    }

    private function sendTelnyx($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Authorization" => sprintf("Bearer %s", $config['api_key']),
            "Content-Type" => "application/json"
        );
        $url = 'https://api.telnyx.com/v2/messages';
        $json = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $telnyxResponse = $session->post('', $headers, $json, array());
            freepbx_log(FPBX_LOG_INFO, $telnyxResponse->body, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }
}