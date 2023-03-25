<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Telnyx extends providerBase {

    public function __construct ()
    {
        parent::__construct();
        $this->name     = _('Telnyx');
        $this->nameRaw  = 'telnyx';
    }
    
    public function sendMedia($provider, $id, $to, $from, $message=null)
    {
        $retval = parent::sendMedia($provider, $id, $to, $from, $message);

        $req = array(
            'from'       => '+'.$from,
            'to'         => '+'.$to,
            'media_urls' => $this->media_urls($id)
        );
        if ($message)
        {
            $req['text'] = $message;
        }
        $this->sendTelnyx($provider, $req, $id);
        return $retval;
    }

    public function sendMessage($provider, $id, $to, $from, $message=null)
    {
        $retval = parent::sendMessage($provider, $id, $to, $from, $message);
        $req = array(
            'from'  => '+'.$from,
            'to'    => '+'.$to,
            'text'  => $message
        );
        $this->sendTelnyx($provider, $req, $id);
        return $retval;
    }

    private function sendTelnyx($provider, $payload, $mid)
    {
        require_once(__DIR__.'/include/telnyx-php/init.php');
        \Telnyx\Telnyx::setApiKey($provider['api_key']);
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