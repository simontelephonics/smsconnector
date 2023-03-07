<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $postdata = file_get_contents("php://input");

    $sms = json_decode($postdata);
    if (empty($sms)) { 
        http_response_code(403); 
        exit; 
    }
    
    if (isset($sms->data)) {
        if (isset($sms->data->event_type) && ($sms->data->event_type == 'message.received')) {
            $from = ltrim($sms->data->payload->from->phone_number, '+'); // strip +
            $to = ltrim($sms->data->payload->to[0]->phone_number, '+'); // strip +
            $text = $sms->data->payload->text;
            $emid = $sms->data->id;
    
            require '/etc/freepbx.conf';
            include $amp_conf['AMPWEBROOT'] . '/admin/modules/sms/Sms.class.php';
            include $amp_conf['AMPWEBROOT'] . '/admin/modules/sms/includes/AdaptorBase.class.php';
            include $amp_conf['AMPWEBROOT'] . '/admin/modules/smsconnector/adaptor/Smsconnector.class.php';
    
            $connector = new \FreePBX\modules\Sms\Adaptor\Smsconnector();
            try {
                $msgid = $connector->getMessage($to, $from, '', $text, null, null, $emid);
            } catch (\Exception $e) {
                http_response_code(500);
                throw new \Exception('Unable to get message: ' .$e->getMessage());
            }
    
            if (isset($sms->data->payload->media[0])) {
                foreach ($sms->data->payload->media as $media) {
                    $img = file_get_contents($media->url);
                    $purl = parse_url($media->url);
                    $name = basename($purl['path']);
                    try {
                        $connector->addMedia($msgid, $name, $img);
                    } catch (\Exception $e) {
                        http_response_code(500);
                        throw new \Exception('Unable to store MMS media: ' .$e->getMessage());
                    }
                }
                
            }
        }
    }
    http_response_code(202);
} else {
    http_response_code(405);
}

