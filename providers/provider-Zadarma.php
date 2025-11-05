<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Zadarma extends providerBase
{
    public function __construct()
    {
        parent::__construct();

        $this->name       = _('Zadarma');
        $this->nameRaw    = 'zadarma';
        $this->APIUrlInfo = 'https://api.zadarma.com';
        $this->APIVersion = 'v1';

        $this->configInfo = [
            'api_key' => [
                'type'        => 'string',
                'label'       => _('API Key'),
                'help'        => _("Enter your Zadarma API Key"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Key'),
            ],
            'api_secret' => [
                'type'        => 'string',
                'label'       => _('API Secret'),
                'help'        => _("Enter your Zadarma API Secret"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Secret'),
            ],
        ];
    }

    public function sendMedia($id, $to, $from, $message = null)
    {
        freepbx_log(FPBX_LOG_INFO, _("Zadarma provider does not support sendMedia (MMS)."));
        return false;
    }

    public function sendMessage($id, $to, $from, $message = null)
    {
        $config    = $this->getConfig($this->nameRaw);
        $apiKey    = trim($config['api_key'] ?? '');
        $apiSecret = trim($config['api_secret'] ?? '');

        if ($apiKey === '' || $apiSecret === '') {
            throw new \Exception(_("Missing Zadarma API credentials."));
        }

        $url = $this->APIUrlInfo . '/'.$this->APIVersion.'/sms/send/';

        $params = [
            'number'  => $to,
            'message' => (string)$message,
        ];
        if (!empty($from)) {
            $params['sender'] = $from;
        }

        $paramStr  = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $signature = base64_encode(hash_hmac('sha1', $paramStr, $apiSecret, true));

        $headers = [
            "Authorization: {$apiKey}",
            "Signature: {$signature}",
            "Content-Type: application/x-www-form-urlencoded",
        ];

        $session = \FreePBX::Curl()->requests($url);

        try {
            $response = $session->post('', $headers, $paramStr, []);
            freepbx_log(FPBX_LOG_INFO, sprintf(_("Zadarma sendMessage HTTP %s"), $response->status_code));

            if (!$response->success) {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $response->status_code, $response->body));
            }

            $this->setDelivered($id);
            return true;
        } catch (\Exception $e) {
            throw new \Exception(sprintf(_('Unable to send message: %s'), $e->getMessage()));
        }
    }

    public function callPublic($connector)
    {
        // One time verification echo
        if (isset($_GET['zd_echo'])) {
            echo $_GET['zd_echo'];
            return 200;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return 405;
        }

        $post = $_POST;

        if (($post['event'] ?? '') !== 'SMS' || empty($post['result'])) {
            return 400;
        }

        $result = $post['result'];
        if (is_string($result)) {
            $decoded = json_decode($result, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $result = $decoded;
            } else {
                return 400;
            }
        }

        $from = isset($result['caller_id'])  ? ltrim((string)$result['caller_id'], '+')  : '';
        $to   = isset($result['caller_did']) ? ltrim((string)$result['caller_did'], '+') : '';
        $text = isset($result['text'])       ? (string)$result['text']                   : '';

        if ($from !== '' && $to !== '' && $text !== '') {
            $this->receiveMessage($connector, $from, $to, $text, null);
            echo 'OK';
            return 202;
        }

        return 400;
    }

    public function getCallbackHelp()
    {
        return _(
            "Configure Zadarma PBX Webhooks to point at:\n" .
            "  https://yourpbx.example.com/admin/modules/smsconnector/public/Zadarma?provider=Zadarma\n\n" .
            "Zadarma will send a one time verification with ?zd_echo=... on GET.\n" .
            "Inbound SMS arrive as POST with event=SMS and result fields caller_id, caller_did, text.\n"
        );
    }
}
