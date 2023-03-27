<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Telnyx extends providerBase {

    public function __construct ()
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
        $retval = parent::sendMedia($id, $to, $from, $message);

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
        return $retval;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $retval = parent::sendMessage($id, $to, $from, $message);
        $req = array(
            'from'  => '+'.$from,
            'to'    => '+'.$to,
            'text'  => $message
        );
        $this->sendTelnyx($req, $id);
        return $retval;
    }

    private function sendTelnyx($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        \Telnyx\Telnyx::setApiKey($config['api_key']);
        try {
            $telnyxResponse = \Telnyx\Message::Create($payload);
            freepbx_log(FPBX_LOG_INFO, $telnyxResponse, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }
}