<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Commio extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('Commio');
        $this->nameRaw  = 'commio';

        $this->configInfo = array(
            'api_key' => array(
                'type'    => 'string',
                'label'   => _('Username'),
                'help'    => _("Enter the Commio API username"),
                'default' => ''
            ),
            'api_secret' => array(
                'type'    => 'string',
                'label'   => _('API Token'),
                'help'    => _("Enter the Commio API token"),
                'default' => ''
            ),
            'account_id' => array(
                'type'    => 'string',
                'label'   => _('Account ID'),
                'help'    => _("Enter the Commio account ID"),
                'default' => ''
            )
        );
    }
    
    public function sendMedia($id, $to, $from, $message=null)
    {
        $req = array(
            'to_did'      => ltrim($to, '1'),
            'from_did'    => ltrim($from, '1'),
            'media_url'   => $this->media_urls($id)[0]
        );
        if ($message)
        {
            $req['message'] = $message;
        }
        $this->sendCommio($req, $id, 'mms');
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'to_did'    => ltrim($to, '1'),
            'from_did'  => ltrim($from, '1'),
            'message'   => $message
        );
        $this->sendCommio($req, $id, 'sms');
        return true;
    }

    private function sendCommio($payload, $mid, $msgType = 'sms')
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            'auth' => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        if ($msgType == 'sms') 
        {
            $headers = array(
                'Content-Type' => 'application/json'
            );
            $reqBody = json_encode($payload);
        }
        else 
        {
            $boundary = md5(time());
            $headers = array(
                'Content-Type' => sprintf('multipart/form-data; boundary=%s', $boundary)
            );
            $reqBody = '';
            foreach ($payload as $formKey => $formValue)
            {
                $reqBody .= "--$boundary\r\n";
                $reqBody .= "Content-Disposition: form-data; name=$formKey\r\n\r\n";
                $reqBody .= "$formValue\r\n"; 
            }
            $reqBody .= "--$boundary--\r\n";
        }

        $url = sprintf('https://api.thinq.com/account/%s/product/origination/%s/send', $config['account_id'], $msgType);
        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $commioResponse = $session->post('', $headers, $reqBody, $options);
            freepbx_log(FPBX_LOG_INFO, $commioResponse->body, true);
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception('Unable to send message: ' .$e->getMessage());
        }
    }
}