<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Brck extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('BRCK');
        $this->nameRaw    = 'brck';
        $this->APIUrlInfo = 'https://portal.brck.com/api/v1/docs';
        $this->APIVersion = 'v1.0';

        $this->configInfo = array(
            'bearer_token' => array(
                'type'      => 'string',
                'label'     => _('Bearer Token'),
                'help'      => _("Enter the BRCK Bearer Token"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Bearer Token'),
            ),
        );
    }

    public function sendMedia($id, $to, $from, $message = null)
    {
        $req = array(
            'to'    => array($to),
            'from'  => $from,
            'media' => $this->media_urls($id)
        );
        if ($message) {
            $attr['text'] = $message;
        }

        $this->sendBrck($req, $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message = null)
    {
        $req = array(
            'to'    => array($to),
            'from'  => $from,
            'text'  => $message
        );
        $this->sendBrck($req, $id);
        return true;
    }

    private function sendBrck($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $config['bearer_token']
        );
        $url = "https://api.brck.com/api/v1/callbacks/outbound/messaging";
        $json = json_encode($payload);
        $session = \FreePBX::Curl()->requests($url);

        try {
            $brckResponse = $session->post('', $headers, $json, array());
            freepbx_log(FPBX_LOG_INFO, sprintf(_('%s responds: HTTP %s, %s'), $this->nameRaw, $brckResponse->status_code, $brckResponse->body), true);

            if (!$brckResponse->success) {
                throw new \Exception(sprintf(_('HTTP %s, %s'), $brckResponse->status_code, $brckResponse->body));
            }
            $this->setDelivered($mid);
        } catch (\Exception $e) {
            throw new \Exception(_('Unable to send message: ') . $e->getMessage());
        }
    }

    public function callPublic($connector)
    {
        return 202;
    }
}
