<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Vitelity extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Vitelity');
        $this->nameRaw    = 'vitelity';
        $this->APIUrlInfo = 'https://apihelp.vitelity.net/';
        $this->APIVersion = 'v1';

        $this->configInfo = array(
            'api_key' => array(
                'type'        => 'string',
                'label'       => _('Vitelity Username'),
                'help'        => _("Enter your Vitelity customer login"),
                'default'     => '',
                'required'    => true,
                'placeholder' => _('Enter Username'),
            ),
            'api_secret' => array(
                'type'        => 'string',
                'label'       => _('Vitelity Password'),
                'help'        => _("Enter your Vitelity password"),
                'default'     => '',
                'required'    => true,
                'class'       => 'confidential',
                'placeholder' => _('Enter Password'),
            )
        );
    }

    public function sendMedia($id, $to, $from, $message=null)
    {
        // NOTE: Vitelity does not support MMS
        return false;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'cmd'  => 'sendsms',
            'src'  => $from,
            'dst'  => $to,
            'msg'  => $message ?? ''
        );

        $this->sendVitelity($req, $id);
        return true;
    }

    private function sendVitelity($payload, $mid): void
    {
        $config = $this->getConfig($this->nameRaw);

        $payload['login'] = $config['api_key'];
        $payload['pass']  = $config['api_secret'];

        // FIX: Build query string and pass as part of URL.
        // Previously $payload was incorrectly passed as the options argument
        // to ->get(), so parameters were never sent to the API.
        $url     = "https://smsout-api.vitelity.net/api.php?" . http_build_query($payload);
        $session = \FreePBX::Curl()->requests($url);

        try {
            $vitelityResponse = $session->get('', [], []);
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $vitelityResponse->status_code, $vitelityResponse->body));

            if (!$vitelityResponse->success) {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $vitelityResponse->status_code, $vitelityResponse->body));
            }

            if (strpos($vitelityResponse->body, 'x[[ok[[x') !== false) {
                $this->setDelivered($mid);
            } else {
                if (strpos($vitelityResponse->body, 'x[[invalid[[x') !== false) {
                    throw new \Exception(_("API error: Unknown send error"));
                } else if (strpos($vitelityResponse->body, 'x[[invaliddata[[x') !== false) {
                    throw new \Exception(_("API error: Invalid data sent"));
                } else {
                    throw new \Exception(sprintf(_("API error: %s"), $vitelityResponse->body));
                }
            }
        } catch (\Exception $e) {
            throw new \Exception(sprintf(_('Unable to send message: %s'), $e->getMessage()));
        }
    }

    public function onConfigSaved(array $dids = []): void
    {
        // Register the webhook for each DID already assigned to this provider.
        // If no DIDs are assigned yet, there is nothing to register.
        if (empty($dids)) {
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s: No DIDs assigned, skipping webhook registration"), $this->nameRaw));
            return;
        }
        $webhookUrl = $this->getWebHookUrl();
        foreach ($dids as $did) {
            try {
                $this->registerWebhookUrl($webhookUrl, $did);
                freepbx_log(FPBX_LOG_INFO, sprintf(_("%s: Webhook URL registered for DID %s"), $this->nameRaw, $did));
            } catch (\Exception $e) {
                freepbx_log(FPBX_LOG_ERROR, sprintf(_("%s: Failed to register webhook URL for DID %s: %s"), $this->nameRaw, $did, $e->getMessage()));
            }
        }
    }

    public function onDIDAssigned(string $did): void
    {
        // Called whenever a DID is assigned or reassigned to this provider.
        try {
            $this->registerWebhookUrl($this->getWebHookUrl(), $did);
            freepbx_log(FPBX_LOG_INFO, sprintf(_("%s: Webhook URL registered for DID %s"), $this->nameRaw, $did));
        } catch (\Exception $e) {
            freepbx_log(FPBX_LOG_ERROR, sprintf(_("%s: Failed to register webhook URL for DID %s: %s"), $this->nameRaw, $did, $e->getMessage()));
        }
    }

    public function registerWebhookUrl($webhookUrl, $did = null): void
    {
        $config = $this->getConfig($this->nameRaw);

        $params = [
            'login' => $config['api_key'],
            'pass'  => $config['api_secret'],
            'cmd'   => 'smsenableurl',
            'url'   => $webhookUrl,
        ];

        if ($did !== null) {
            // Vitelity expects a 10-digit NANP number; strip the leading country code if present.
            $params['did'] = preg_replace('/^1([2-9]\d{9})$/', '$1', $did);
        }

        // NOTE: smsenableurl uses a different base URL than sendsms
        $url      = "https://api.vitelity.net/api.php?" . http_build_query($params);
        $session  = \FreePBX::Curl()->requests($url);
        $response = $session->get('', [], []);

        freepbx_log(FPBX_LOG_INFO, sprintf(_("%s smsenableurl responds: HTTP %s, %s"), $this->nameRaw, $response->status_code, $response->body));

        if (strpos($response->body, 'x[[ok[[x') === false) {
            throw new \Exception(sprintf(_('Failed to register webhook URL: %s'), $response->body));
        }
    }

    public function callPublic($connector)
    {
        $return_code = 202;

        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            // Vitelity validates the webhook URL with a GET request before registering it.
            // Respond with 'ok' so the smsenableurl command accepts the URL as valid.
            echo 'ok';
            return 200;
        }

        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $postdata = $_POST;

            freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s) in: %s"), $this->nameRaw, print_r($postdata, true)));

            if (empty($postdata)) {
                $return_code = 403;
            } else {
                if (isset($postdata['src']) && isset($postdata['dst']) && isset($postdata['msg'])) {
                    $from = $postdata['src'];
                    $to   = $postdata['dst'];
                    $text = $postdata['msg'];
                    $emid = $postdata['msgid'] ?? null;

                    // Normalize 10-digit NANP numbers by prepending country code
                    if (preg_match('/^([2-9]\d{2}[2-9]\d{6})$/', $from)) {
                        $from = '1' . $from;
                    }
                    if (preg_match('/^([2-9]\d{2}[2-9]\d{6})$/', $to)) {
                        $to = '1' . $to;
                    }

                    try {
                        $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                        $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
                        freepbx_log(FPBX_LOG_INFO, sprintf(_("Processed inbound message from %s to %s"), $from, $to));
                    } catch (\Exception $e) {
                        throw new \Exception(sprintf(_('Unable to process inbound message: %s'), $e->getMessage()));
                    }

                    // FIX: Vitelity requires the literal string 'ok' in the response body,
                    // otherwise it will keep retrying delivery of the same message.
                    echo 'ok';
                    $return_code = 200;
                } else {
                    freepbx_log(FPBX_LOG_WARNING, sprintf(_("Webhook (%s): Missing required parameters"), $this->nameRaw));
                    $return_code = 400;
                }
            }
        } else {
            $return_code = 405;
        }

        return $return_code;
    }
}
