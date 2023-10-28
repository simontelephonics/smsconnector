<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Voxtelesys extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name       = _('Voxtelesys');
        $this->nameRaw    = 'voxtelesys';
        $this->APIUrlInfo = 'https://smsapi.voxtelesys.com';
        $this->APIVersion = 'v1';

        $this->configInfo = array(
            'api_key' => array(
                'type'      => 'string',
                'label'     => _('API Key'),
                'help'      => _("Enter the Voxtelesys v1 API key"),
                'default'   => '',
                'required'  => true,
                'placeholder' => _('Enter Key'),
            )
        );
    }

    public function sendMedia($id, $to, $from, $message=null)
    {
        $req = array(
            'from'  => '+'.$from,
            'to'    => '+'.$to,
            'media' => $this->media_urls($id)
        );
        if ($message)
        {
            $req['body'] = $message;
        }
        $this->sendVoxtelesys($req, $id);
        return true;
    }

    public function sendMessage($id, $to, $from, $message=null)
    {
        $req = array(
            'from'  => '+'.$from,
            'to'    => '+'.$to,
            'body'  => $message
        );
        $this->sendVoxtelesys($req, $id);
        return true;
    }

    private function sendVoxtelesys($payload, $mid)
    {
        $config = $this->getConfig($this->nameRaw);

        $headers = array(
            "Authorization" => sprintf("Bearer %s", $config['api_key']),
            "Content-Type" => "application/json"
        );
        $url = 'https://smsapi.voxtelesys.net/api/v1/sms';
        $json = json_encode($payload);

        $session = \FreePBX::Curl()->requests($url);
        try 
        {
            $voxtelesysResponse = $session->post('', $headers, $json, array());
            freepbx_log(FPBX_LOG_INFO, sprintf("%s responds: HTTP %s, %s", $this->nameRaw, $voxtelesysResponse->status_code, $voxtelesysResponse->body), true);
            if (! $voxtelesysResponse->success)
            {
                throw new \Exception("HTTP $voxtelesysResponse->status_code, $voxtelesysResponse->body");
            }
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
            $postdata = file_get_contents("php://input");
            $sms      = json_decode($postdata);

            freepbx_log(FPBX_LOG_INFO, sprintf("Webhook (%s) in: %s", $this->nameRaw, print_r($postdata, true)));
            if (empty($sms)) 
            { 
                $return_code = 403;
            }
            else
            {
                if (isset($sms->type) && ($sms->type == 'mo')) 
                {
                    $from = ltrim($sms->from, '+'); // strip +
                    $to   = ltrim($sms->to, '+'); // strip +
                    $body = $sms->body;
                    $emid = $sms->id;

                    try 
                    {
                        $msgid = $connector->getMessage($to, $from, '', $body, null, null, $emid);
                    } 
                    catch (\Exception $e) 
                    {
                        throw new \Exception(sprintf('Unable to get message: %s', $e->getMessage()));
                    }
            
                    if (isset($sms->media[0])) 
                    {
                        foreach ($sms->media as $media) 
                        {
                            $img  = file_get_contents($media);
                            $purl = parse_url($media->url);
                            $name = $msgid . basename($purl['path']);
                            try 
                            {
                                $connector->addMedia($msgid, $name, $img);
                            } 
                            catch (\Exception $e) 
                            {
                                throw new \Exception(sprintf('Unable to store MMS media: %s', $e->getMessage()));
                            }
                        }
                    }
                    $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $body, null, 'Smsconnector', $emid);
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