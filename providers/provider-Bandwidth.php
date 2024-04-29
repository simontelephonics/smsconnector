<?php

namespace FreePBX\modules\Smsconnector\Provider;

class Bandwidth extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Bandwidth');
        $this->nameRaw    = 'bandwidth';
        $this->APIUrlInfo = 'https://dev.bandwidth.com/apis/messaging/';
        $this->APIVersion = 'v2.0';

        $this->configInfo = array(
            'api_token' => array(
                'type'      => 'string',
                'label'     => _('API Token'),
                'help'      => _("Enter the Bandwidth API Token"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter API Token'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Secret'),
                'help'      => _("Enter the Bandwidth API Secret"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter API Secret'),
            ),
            'account_id' => array(
                'type'      => 'string',
                'label'     => _('Account ID'),
                'help'      => _("Enter the Bandwidth Account ID (7 numbers)"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Account ID'),
            ),
            'application_id' => array(
                'type'      => 'string',
                'label'     => _('Application ID'),
                'help'      => _("Enter the Bandwidth Application ID"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Application ID'),
            ),
            'callback_user_id' => array(
                'type'      => 'string',
                'label'     => _('Callback User ID'),
                'help'      => _("Enter the Callback User ID (optional)"),
                'default'   => '',
                'required'  => false,
                'placeholder' => _('Enter Callback User ID (optional)'),
            ),
            'callback_password' => array(
                'type'      => 'string',
                'label'     => _('Callback Password'),
                'help'      => _("Enter the Callback Password (optional)"),
                'default'   => '',
                'required'  => false,
                'class'     => 'confidential',
                'placeholder' => _('Enter Callback Password (optional)'),
            )
        );
    }

    public function sendMedia($id, $to, $from, $message = null)
    {
        $req = array(
            "applicationId" => $this->getConfig($this->nameRaw)['application_id'],
            "to"    => array($to),
            "from"  => $from,
            "media" => $this->media_urls($id)
        );
        if ($message) {
            $attr['text'] = $message;
        }

        $this->sendBandwidth($req, $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message = null)
    {
        $req = array(
            "applicationId" => $this->getConfig($this->nameRaw)['application_id'],
            "to"    => array($to),
            "from"  => $from,
            "text"  => $message
        );
        $this->SendBandwidth($req, $id);
        return true;
    }

    private function sendBandwidth($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Content-Type" => "application/json",
            "Authorization" => "Basic " . base64_encode($config['api_token'] . ":" . $config['api_secret'])
        );
        $url = "https://messaging.bandwidth.com/api/v2/users/" . $config['account_id'] . "/messages";
        $json = json_encode($payload);
        $session = \FreePBX::Curl()->requests($url);

        try {
            $bandwidthResponse = $session->post('', $headers, $json, array());
            freepbx_log(FPBX_LOG_INFO, sprintf("%s responds: HTTP %s, %s", $this->nameRaw, $bandwidthResponse->status_code, $bandwidthResponse->body), true);

            if (!$bandwidthResponse->success) {
                throw new \Exception("HTTP $bandwidthResponse->status_code, $bandwidthResponse->body");
            }
            $this->setDelivered($mid);
        } catch (\Exception $e) {
            throw new \Exception('Unable to send message: ' . $e->getMessage());
        }
    }

    public function callPublic($connector)
    {
        $config = $this->getConfig($this->nameRaw);

        // Check if callback authentication is enabled/expected
        // If it is, check that the user and password match the values coming from bandwidth
        if ($config['callback_user_id'] && $config['callback_password']) {
            
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            
            if (!$user || !$password || $user !== $config['callback_user_id'] || $password !== $config['callback_password']) {
                freepbx_log(FPBX_LOG_INFO, sprintf("Callback authentication failed for %s", $this->nameRaw));
                freepbx_log(FPBX_LOG_INFO, sprintf("From Bandwidth. Pass: %s, User: %s", $password, $user));
                freepbx_log(FPBX_LOG_INFO, sprintf("Expected pass: %s, Expected user: %s", $config['callback_password'], $config['callback_user_id']));
                return 401;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            return 405;
        }

        $postdata = file_get_contents("php://input");
        $sms      = json_decode($postdata)[0];

        freepbx_log(FPBX_LOG_INFO, sprintf("Webhook (%s) in: %s", $this->nameRaw, print_r($postdata, true)));

        if (empty($sms)) {
            return 403;
        }

        if (isset($sms)) {
            if (isset($sms->type) && ($sms->type == 'message-received')) {

                $from = ltrim($sms->message->from, '+'); // strip + if exists
                $to   = ltrim($sms->to, '+'); // The sms->to will always just be one number, but if we wanted to support group messaging we'd need to look at sms->message->to which holds an array of all numbers in the convo
                $text = $sms->message->text;
                $emid = $sms->message->id;

                try {
                    $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                } catch (\Exception $e) {
                    throw new \Exception(sprintf('Unable to get message: %s', $e->getMessage())); 
                }

                if (isset($sms->message->media[0])) {
                    // Create authentication header/context required to grab media from Bandwidth
                    $context = stream_context_create([
                        "http" => [
                            "header" => "Authorization: Basic " . base64_encode($config['api_token'] . ":" . $config['api_secret'])
                        ]
                    ]);

                    foreach ($sms->message->media as $media) {
                        $img = file_get_contents($media, false, $context);
                        $purl = parse_url($media);
                        $name = $msgid . basename($purl['path']);

                        try {
                            $connector->addMedia($msgid, $name, $img);
                        } catch (\Exception $e) {
                            throw new \Exception(sprintf('Unable to store MMS media: %s', $e->getMessage()));
                        }
                    }
                }

                $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
            } else if (isset($sms->type)) {
                // Likely a callback for delivery status
                // See https://dev.bandwidth.com/docs/messaging/webhooks/
                // These can be disabled when setting up the application within Bandwidth
                // It would be good to support these in the future
                // We still reply with a 202 to appease Bandwidth
                freepbx_log(FPBX_LOG_INFO, sprintf("Incoming message of unknown type %s. Contents: %s", $sms->type, print_r($sms, true)));
            }
        }

        return 202;
    }
}
