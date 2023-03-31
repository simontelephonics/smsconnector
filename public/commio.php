<?php
if ($_SERVER['REQUEST_METHOD'] === "POST") 
{
    $postdata = $_POST;

    if (empty($postdata)) 
    { 
        http_response_code(403); 
        exit; 
    }
    
    if (isset($postdata['type']) && isset($postdata['from']) && isset($postdata['to']) && isset($postdata['message'])) 
    {
        // Commio will send either 11-digit or 10-digit NANP. If 10 then we will add the 1
        $from = (strlen($postdata['from']) == 10) ? '1'.$postdata['from'] : $postdata['from'];
        $to = (strlen($postdata['to']) == 10) ? '1'.$postdata['to'] : $postdata['to'];
        $text = '';

        if (($postdata['type'] == 'sms') || ($postdata['type'] == 'mms' && (stripos($postdata['message'], 'http') !== 0))) 
        {
            // SMS will have text in the 'message' field and MMS will have either a URL or text, so we have to check
            $text = $postdata['message'];
        }

        // load FreePBX
        $bootstrap_settings['freepbx_auth'] = false;
        require '/etc/freepbx.conf';
        $freepbx = \FreePBX::Create();
        freepbx_log(FPBX_LOG_INFO, "Commio webhook in: " . print_r($postdata, true));
        $connector = $freepbx->Sms->loadAdaptor('Smsconnector');

        try 
        {
            $msgid = $connector->getMessage($to, $from, '', $text, null, null, null);
        } 
        catch (\Exception $e) 
        {
            http_response_code(500);
            throw new \Exception('Unable to get message: ' .$e->getMessage());
            exit;
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
                http_response_code(500);
                throw new \Exception('Unable to store MMS media: ' .$e->getMessage());
                exit;
            }
        }
            
        $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $text, null, 'Smsconnector', $emid);
    }
    http_response_code(202);
} 
else 
{
    http_response_code(405);
}

