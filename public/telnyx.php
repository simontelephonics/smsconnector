<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") 
{
    $postdata = file_get_contents("php://input");

    $sms = json_decode($postdata);
    if (empty($sms)) 
    { 
        http_response_code(403); 
        exit; 
    }
    
    if (isset($sms->data)) 
    {
        if (isset($sms->data->event_type) && ($sms->data->event_type == 'message.received')) 
        {
            $from = ltrim($sms->data->payload->from->phone_number, '+'); // strip +
            $to = ltrim($sms->data->payload->to[0]->phone_number, '+'); // strip +
            $text = $sms->data->payload->text;
            $emid = $sms->data->id;
    
            // load FreePBX
            $bootstrap_settings['freepbx_auth'] = false;
            require '/etc/freepbx.conf';
            $freepbx = \FreePBX::Create();
            freepbx_log(FPBX_LOG_INFO, "Telnyx webhook in: " . print_r($postdata, true));
            $connector = $freepbx->Sms->loadAdaptor('Smsconnector');
    
            try 
            {
                $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
            } 
            catch (\Exception $e) 
            {
                http_response_code(500);
                throw new \Exception('Unable to get message: ' .$e->getMessage());
            }
    
            if (isset($sms->data->payload->media[0])) 
            {
                foreach ($sms->data->payload->media as $media) 
                {
                    $img = file_get_contents($media->url);
                    $purl = parse_url($media->url);
                    $name = basename($purl['path']);
                    try 
                    {
                        $connector->addMedia($msgid, $name, $img);
                    } 
                    catch (\Exception $e) 
                    {
                        http_response_code(500);
                        throw new \Exception('Unable to store MMS media: ' .$e->getMessage());
                    }
                }
            }
            
            $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
        }
    }
    http_response_code(202);
} 
else 
{
    http_response_code(405);
}

