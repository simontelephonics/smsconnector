<?php
namespace FreePBX\modules\Smsconnector\Provider;

class Didforsale extends providerBase 
{
    public function __construct()
    {
        parent::__construct();
        $this->name     = _('DIDforsale');
        $this->nameRaw  = 'didforsale';
        $this->APIUrlInfo = 'https://myapi.didforsale.com/documentation/';
        $this->APIVersion = 'V4';

        $this->configInfo = array(
            'apikey' => array(
                'type'        => 'string', 
                'label'       => _('API Key'),
                'help'        => _("Your DIDforsale API Key."),
                'default'     => '',
                'class'       => '',
                'required'    => true, 
                'placeholder' => _('Enter your DIDforsale API Key'),
            ),
            'api_secret' => array(
                'type'        => 'string', 
                'label'       => _('Access Token'),
                'help'        => _("Your DIDforsale Access Token (for API v4.0+)."),
                'default'     => '',
                'class'       => 'confidential',
                'required'    => true, 
                'placeholder' => _('Enter your DIDforsale Access Token'),
            ),
        );
    }
     
    public function sendMedia($id, $to, $from, $message=null)
    {
        // DIDforsale supports MMS via media_urls parameter in the Send SMS API
        // Media URLs are included in the request alongside text message
        return $this->sendMessage($id, $to, $from, $message, true);
    }

    public function sendMessage($id, $to, $from, $message=null, $includeMedia=false)
    {
        $config = $this->getConfig($this->nameRaw);
        
        if (empty($config['apikey']) || empty($config['api_secret'])) {
            return false;
        }

        // DIDforsale API Endpoint (V4 supports JSON with media_urls parameter)
        $url = 'https://api.didforsale.com/didforsaleapi/index.php/api/V4/SMS/Send';

        // Prepare Data - SMS parameters in body
        $data = array(
            'from'   => $from,
            'to'     => $to,
            'text'   => $message
        );
        
        // Add media URLs if sending MMS
        if ($includeMedia) {
            $mediaUrls = $this->media_urls($id);
            if (!empty($mediaUrls)) {
                $data['media_urls'] = $mediaUrls;
            }
        }

        // DIDforsale API v4.0 requires authentication via HTTP headers
        $headers = array(
            'Content-Type: application/json',
            'APIKEY: ' . $config['apikey'],
            'accesstoken: ' . $config['api_secret']
        );

        // Send Request via CURL with JSON body and auth headers
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Parse JSON response and check for success
        $responseData = json_decode($response, true);
        if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === true) {
            $this->setDelivered($id);
            return true;
        }

        // Log error response for debugging
        freepbx_log(FPBX_LOG_INFO, sprintf(_('DIDforsale Send Failed: HTTP %s, %s'), $httpCode, $response));

        return false;
    }

    public function callPublic($connector)
    {
        // DIDforsale sends parameters via GET or POST (usually POST)
        // Parameters are: 'from', 'to', 'text'
        
        $request = $_REQUEST;
        $return_code = 200;

        if (isset($request['from']) && isset($request['to']) && isset($request['text'])) {
            
            $from    = $request['from'];
            $to      = $request['to'];
            $message = $request['text'];

            freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s) in: from=%s, to=%s, text=%s"), $this->nameRaw, $from, $to, $message));

            try {
                // Store inbound message to database and get message ID
                $msgid = $connector->getMessage($to, $from, '', $message, null, null, null);

                // Emit event to notify FreePBX system and connected modules (UCP, dialplan, etc.)
                $connector->emitSmsInboundUserEvt($msgid, $to, $from, '', $message, null, 'Smsconnector', null);

                freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s): SMS received and processed (msgid=%s)"), $this->nameRaw, $msgid));
                $return_code = 200;
            } catch (\Exception $e) {
                freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s): Error processing SMS: %s"), $this->nameRaw, $e->getMessage()));
                $return_code = 500;
            }
        }

        // Return response code to DIDforsale so they know if we processed it
        return $return_code;
    }
}