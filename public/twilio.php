<?php

if (empty($_SERVER['HTTP_X_TWILIO_SIGNATURE'])) { exit; }

if ($_SERVER['REQUEST_METHOD'] === "POST") 
{
    $postdata = $_POST;
 
    $to = ltrim($postdata['To'], '+');
    $from = ltrim($postdata['From'], '+');
    $text = $postdata['Body'];
    $emid = $postdata['SmsMessageSid'];

    // load FreePBX
    $bootstrap_settings['freepbx_auth'] = false;
    require '/etc/freepbx.conf';
    $freepbx = \FreePBX::Create();
    freepbx_log(FPBX_LOG_INFO, "Twilio webhook in: " . print_r($postdata, true));
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
    
    if (isset($postdata['NumMedia']) && ($postdata['NumMedia'] > 0)) 
    {
        for ($x=0;$x<=$postdata['NumMedia'];$x++) 
        {
            $img = file_get_contents($postdata["MediaUrl$x"]);
            foreach ($http_response_header as $header) 
            {
                if (preg_match('/Content-Disposition.*filename="(.*)"/', $header, $matches)) 
                {
                    $name = $matches[1];
                }
            }

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

    header('Content-Type: application/xml');
    echo '<Response/>';

} 
else 
{
    http_response_code(405);
}

