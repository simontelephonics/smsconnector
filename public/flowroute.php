<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $postdata = file_get_contents("php://input");

    $sms = json_decode($postdata);
    if (empty($sms)) { 
        http_response_code(403); 
        exit; 
    }
    
    if (isset($sms->data)) {
        if (isset($sms->data->type) && ($sms->data->type == 'message')) {
            $from = ltrim($sms->data->attributes->from, '+'); // strip + if exists
            $to = ltrim($sms->data->attributes->to, '+'); // strip + if exists
            $text = $sms->data->attributes->body;
            $emid = $sms->data->id;
    
            // load FreePBX
            $bootstrap_settings['freepbx_auth'] = false;
            require '/etc/freepbx.conf';
            $freepbx = \FreePBX::Create();
            $connector = $freepbx->Sms->loadAdaptor('Smsconnector');
    
            try {
                $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
            } catch (\Exception $e) {
                http_response_code(500);
                throw new \Exception('Unable to get message: ' .$e->getMessage());
            }
    
            if (isset($sms->included[0])) {
                foreach ($sms->included as $media) {
                    if ($media->type == 'media') {
                        $img = file_get_contents($media->attributes->url);
                        $name = $media->attributes->file_name;
                    
                        try {
                            $connector->addMedia($msgid, $name, $img);
                        } catch (\Exception $e) {
                            http_response_code(500);
                            throw new \Exception('Unable to store MMS media: ' .$e->getMessage());
                        }
                    }
                }
            }
            
            $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
        }
    }
    http_response_code(202);
} else {
    http_response_code(405);
}

