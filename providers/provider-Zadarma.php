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

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string',
                'label'       => _('API Key'),
                'help'        => _("Enter your Zadarma API Key"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Key'),
            ),
            'api_secret' => array(
                'type'        => 'string',
                'label'       => _('API Secret'),
                'help'        => _("Enter your Zadarma API Secret"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Secret'),
            ),
        );
    }

    public function sendMedia($id, $to, $from, $message = null)
    {
        // Zadarma API does not support MMS, so just log and return false.
        freepbx_log(FPBX_LOG_INFO, _("Zadarma provider does not support sendMedia (MMS)."));
        return false;
    }

    public function sendMessage($id, $to, $from, $message = null)
    {
        $config = $this->getConfig($this->nameRaw);

        // Prepare API parameters
        $params = array(
            'number'  => $to,
            'message' => $message,
        );

        // Prepare headers with API key/secret
        $apiKey    = $config['api_key'];
        $apiSecret = $config['api_secret'];

        $url = 'https://api.zadarma.com/v1/sms/send/';

        // Prepare the signature
        $paramStr = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $signature = base64_encode(hash_hmac('sha1', $paramStr, $apiSecret, true));

        $headers = array(
            "Authorization: $apiKey",
            "Signature: $signature",
            "Content-Type: application/x-www-form-urlencoded"
        );

        $session = \FreePBX::Curl()->requests($url);
        try {
            $response = $session->post('', $headers, $paramStr, array());
            freepbx_log(FPBX_LOG_INFO, sprintf(_("Zadarma responds: HTTP %s, %s"), $response->status_code, $response->body));
            if (!$response->success) {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $response->status_code, $response->body));
            }
            $this->setDelivered($id);
        } catch (\Exception $e) {
            throw new \Exception(sprintf(_('Unable to send message: %s'), $e->getMessage()));
        }
        return true;
    }

    public function callPublic($connector)
    {
        $return_code = 202;

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $postdata = $_POST;
            // Log incoming post for debug (remove/comment in production)
            file_put_contents('/tmp/sms_webhook_debug.log', date('c') . " " . print_r($postdata, true) . "\n", FILE_APPEND);

            if (!empty($postdata['event']) && $postdata['event'] === 'SMS') {
                $result = $postdata['result'];

                // Zadarma may send JSON string or array
                if (is_string($result)) {
                    $result = json_decode($result, true);
                }

                $from = isset($result['caller_id']) ? ltrim($result['caller_id'], '+') : '';
                $to   = isset($result['caller_did']) ? ltrim($result['caller_did'], '+') : '';
                $text = isset($result['text']) ? $result['text'] : '';

                // Save to debug as well
                file_put_contents('/tmp/sms_webhook_debug.log', "SMS From: $from To: $to Msg: $text\n", FILE_APPEND);

                if ($from && $to && $text) {
                    try {
                        $msgid = $connector->getMessage($to, $from, '', $text, null, null, null);
                        $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', null);
                    } catch (\Exception $e) {
                        throw new \Exception(sprintf(_('Unable to process message: %s'), $e->getMessage()));
                    }
                }
            } else {
                $return_code = 400; // Bad Request
            }
        } else {
            $return_code = 405; // Method Not Allowed
        }

        return $return_code;
    }
}
