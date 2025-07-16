<?php
if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']); //verification request from Zadarma with the value

if (strstr($_SERVER['QUERY_STRING'], ';') !== FALSE) // using ; as separator
{
    $qs = str_replace(';', '&', $_SERVER['QUERY_STRING']);
    parse_str($qs, $sms);
} else {
    $sms = $_GET;
}
if (! empty($sms['provider']))
{
    $provider = $sms['provider'];

    // load FreePBX
    $bootstrap_settings['freepbx_auth'] = false;
    require '/etc/freepbx.conf';

    $freepbx            = \FreePBX::Create();
    $smsconnector       = $freepbx->Smsconnector();
    $listProviders      = $smsconnector->listProviders();
    $availableProviders = $smsconnector->getAvailableProviders();

    if (in_array($provider, array_keys($availableProviders)))
    {
        $connector = $freepbx->Sms->loadAdaptor('Smsconnector');
        try 
        {
            $code = $availableProviders[$provider]['class']->callPublic($connector);
            if ($code !== "")
            {
                freepbx_log(FPBX_LOG_INFO, sprintf(_("Webhook (%s): Return Code %s"), $provider, $code));
                http_response_code($code);
            }
        } 
        catch (\Exception $e) 
        {
            freepbx_log(FPBX_LOG_INFO, sprintf(_("Exception Webhook (%s): %s"), $provider, $e->getMessage()));
            http_response_code(500);
        }
    }
    else if (in_array($provider, $listProviders))
    {
        freepbx_log(FPBX_LOG_INFO, sprintf(_("Error Webhook (%s): The provider is not available!"), $provider));
        http_response_code(421);
    }
    else
    {
        freepbx_log(FPBX_LOG_INFO, sprintf(_("Error Webhook (%s): The provider does not exist!"), $provider));
        http_response_code(503);
    }
}
else
{
    http_response_code(406);
}
