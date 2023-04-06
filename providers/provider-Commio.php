<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Commio extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Commio');
        $this->nameRaw    = 'commio';
        $this->APIUrlInfo = 'https://apidocs.thinq.com/#bac2ace6-7777-47d8-931e-495b62f01799';
        $this->APIVersion = '';

        $this->configInfo = array(
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('Username'),
                'help'      => _("Enter the Commio API username"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Username'),
            ),
            'api_secret' => array(
                'type'      => 'string',
                'label'     => _('API Token'),
                'help'      => _("Enter the Commio API token"),
                'default'   => '',
                'required'  => true,
                'class'     => 'confidential',
                'placeholder' => _('Enter Token'),
            ),
            'account_id' => array(
                'type'      => 'string',
                'label'     => _('Account ID'),
                'help'      => _("Enter the Commio account ID"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Account ID'),
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

    public function callPublic($connector)
    {
        $return_code = 202;

        if ($_SERVER['REQUEST_METHOD'] === "POST") 
        {
            $postdata = $_POST;

            freepbx_log(FPBX_LOG_INFO, sprintf("Webhook (%s) in: %s", $this->nameRaw, print_r($postdata, true)));
            if (empty($postdata)) 
            { 
                $return_code = 403;
            }
            else
            {
                if (isset($postdata['type']) && isset($postdata['from']) && isset($postdata['to']) && isset($postdata['message']))
                {
                    // Commio will send either 11-digit or 10-digit NANP. If 10 then we will add the 1
                    $from = (strlen($postdata['from']) == 10) ? '1'.$postdata['from'] : $postdata['from'];
                    $to   = (strlen($postdata['to']) == 10)   ? '1'.$postdata['to']   : $postdata['to'];
                    $emid = $postdata['id'];
                    $text = '';

                    if (($postdata['type'] == 'sms') || ($postdata['type'] == 'mms' && (stripos($postdata['message'], 'http') !== 0))) 
                    {
                        // SMS will have text in the 'message' field and MMS will have either a URL or text, so we have to check
                        $text = $postdata['message'];
                    }

                    try 
                    {
                        $msgid = $connector->getMessage($to, $from, '', $text, null, null, null);
                    } 
                    catch (\Exception $e) 
                    {
                        throw new \Exception(sprintf('Unable to get message: %s', $e->getMessage()));
                    }

                    if ($postdata['type'] == 'mms' && (stripos($postdata['message'], 'http') === 0))
                    {
                        // MMS with a URL in the message field: fetch the media
                        $img = file_get_contents($postdata['message']);
                        $purl = parse_url($postdata['message']);
                        $name = basename($purl['path']);
                        try 
                        {
                            $connector->addMedia($msgid, $name, $img);
                        } 
                        catch (\Exception $e) 
                        {
                            throw new \Exception(sprintf('Unable to store MMS media: %s', $e->getMessage()));
                        }
                    }
                    
                    $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
                }
                $return_code = 202;
            }
        } 
        else 
        {
            $return_code = 405;
        }
        return $return_code;
    }
}