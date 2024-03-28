<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Signalwire extends providerBase
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('SignalWire');
        $this->nameRaw    = 'signalwire';
        $this->APIUrlInfo = 'https://developer.signalwire.com/compatibility-api/rest/create-a-message';
        $this->APIVersion = '2010-04-01';

        $this->configInfo = array(
            'subdomain' => array(
                'type'      => 'string',
                'label'     => _('SignalWire Subdomain'),
                'help'      => _('Enter your unique SignalWire subdomain, the first part of subdomain.signalwire.com'),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter SignalWire Subdomain'),
            ),
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('Project ID'),
                'help'      => _("Enter the SignalWire project ID"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Project ID'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Token'),
                'help'      => _("Enter the SignalWire API Token"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter Token'),
            )
        );
    }

    public function sendMedia($id, $to, $from, $message=null)
    {
        // this manual generation of the www-form-data request is because SignalWire wants
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
        $this->sendSignalwire(implode('&', $req), $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'From' => '+'.$from,
            'To'   => '+'.$to,
            'Body' => $message
        );
        $this->sendSignalwire($req, $id);
        return true;
    }

    private function sendSignalwire($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $options = array(
            "auth" => array(
                $config['api_key'],
                $config['api_secret']
            )
        );
        $url = sprintf('https://%s.signalwire.com/api/laml/%s/Accounts/%s/Messages', $config['subdomain'], $this->APIVersion, $config['api_key']);
        $session = \FreePBX::Curl()->requests($url);
        try
        {
            $signalwireResponse = $session->post('', null, $payload, $options);
            $this->LogInfo(sprintf(_("%s responds: HTTP %s, %s"), $this->nameRaw, $signalwireResponse->status_code, $signalwireResponse->body));
            if (! $signalwireResponse->success)
            {
                throw new \Exception(sprintf(_("HTTP %s, %s"), $signalwireResponse->status_code, $signalwireResponse->body));
            }
            $this->setDelivered($mid);
        }
        catch (\Exception $e)
        {
            throw new \Exception(sprintf(_('Unable to send message: %s'), $e->getMessage()));
        }
    }

    public function callPublic($connector)
    {
        $return_code = 202;
        if (empty($_SERVER['HTTP_X_SIGNALWIRE_SIGNATURE']))
        {
            $return_code = 412;
        }
        else
        {
            if ($_SERVER['REQUEST_METHOD'] === "POST")
            {
                $postdata = $_POST;

                $this->LogInfo(sprintf(_("Webhook (%s) in: %s"), $this->nameRaw, print_r($postdata, true)));
                if (empty($postdata))
                {
                    $return_code = 403;
                }
                else
                {
                    $to   = ltrim($postdata['To'], '+');
                    $from = ltrim($postdata['From'], '+');
                    $text = $postdata['Body'];
                    $emid = $postdata['SmsMessageSid'];

                    try
                    {
                        $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
                    }
                    catch (\Exception $e)
                    {
                        throw new \Exception(sprintf(_('Unable to get message: %s'), $e->getMessage()));
                    }

                    if (isset($postdata['NumMedia']) && ($postdata['NumMedia'] > 0))
                    {
                        $config = $this->getConfig($this->nameRaw);
                        $options = array(
                            "auth" => array(
                                $config['api_key'],
                                $config['api_secret']
                            )
                        );
                        for ($x=0;$x<$postdata['NumMedia'];$x++)
                        {
                            $session = \FreePBX::Curl()->requests($postdata["MediaUrl$x"]);

                            try
                            {
                                $img = $session->get('', array(), $options);
                            }
                            catch (\Exception $e)
                            {
                                throw new \Exception(sprintf(_('Unable to get media file: %s'), $e->getMessage()));
                            }

                            $name = "media";
                            $purl = (!empty($img->url)) ? parse_url($img->url) : null;
                            if ($purl)
                            {
                                $name = $msgid . basename($purl['path']);
                            }

                            try
                            {
                                $connector->addMedia($msgid, $name, $img->body);
                            }
                            catch (\Exception $e)
                            {
                                throw new \Exception(sprintf(_('Unable to store MMS media: %s'), $e->getMessage()));
                            }
                        }
                    }

                    $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);

                    header('Content-Type: application/xml');
                    echo '<Response/>';
                    exit;
                }
            }
            else
            {
                $return_code = 405;
            }
        }
        return $return_code;
    }
}