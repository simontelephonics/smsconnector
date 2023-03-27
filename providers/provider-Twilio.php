<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Twilio extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Twilio');
        $this->nameRaw  = 'twilio';

        $this->configInfo = array(
            'api_key' => array(
                'type'    => 'string',
                'label'   => _('Account SID'),
                'help'    => _("Enter the Twilio account SID"),
                'default' => ''
            ),
            'api_secret' => array(
                'type'    => 'string',
                'label'   => _('Auth Token'),
                'help'    => _("Enter the Twilio Auth Token"),
                'default' => ''
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        // this manual generation of the www-form-data request is because Twilio wants
        // MediaUrl specified multiple times in the request data, not as a MediaUrl[] array
        $req = array(
            'From=' . urlencode("+$from"),
            'To=' . urlencode("+$to")
        );
        $media_urls = $this->media_urls($id);
        foreach ($media_urls as $media_url)
        {
            $req[] = 'MediaUrl=' . urlencode($media_url);
        }
        if ($message)
        {
            $req[] = 'Body=' . urlencode($message);
        }
        $this->sendTwilio(implode('&', $req), $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'From' => '+'.$from,
            'To'   => '+'.$to,
            'Body' => $message
        );
        $this->sendTwilio($req, $id);
        return true;
    }

    private function sendTwilio($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            "auth" => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $config['api_key']);
        $session = \FreePBX::Curl()->requests($url);
        try
        {
            $twilioResponse = $session->post('', null, $payload, $options);
            freepbx_log(FPBX_LOG_INFO, $twilioResponse->body, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }
}