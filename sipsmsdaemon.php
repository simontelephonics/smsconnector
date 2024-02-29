#!/usr/bin/env php
<?php

// load FreePBX
$bootstrap_settings['include_compress'] = false;
$restrict_mods = array('smsconnector' => true);
require_once '/etc/freepbx.conf';

global $astman;

if ($astman->connected())
{
    $astman->Events('on');
    $astman->add_event_handler('UserEvent', function($event, $data, $server, $port) { userEventHandler($data); });
}

out('Listening for inbound SMS events...');
while (true)
{
    $astman->wait_response(true);
    while (! $astman->connected())
    {
        out('Lost connection to astman. Reconnecting...');
        sleep(2);
        $astman->reconnect();
    }
}

exit;

function userEventHandler($data)
{
    global $astman;

    if ($data['UserEvent'] == 'sms-inbound')
    {
        // I'm not sure about this. I tried instantiating it in the main part of the script but eventually the
        // database connection would time out. Suggestions welcomed.
        $smsconnector       = \FreePBX::Create()->Smsconnector();

        // lookup users for DID
        $uids = $smsconnector->getUsersByDid($data['to']);

        foreach ($uids as $uid)
        {
            // get device that can receive SMS
            $device = $smsconnector->getSIPMessageDeviceByUserID($uid);

            if ($device)
            {
                if ($data['to'] != $smsconnector->getSipDefaultDidByUid($uid))
                {
                    // format the caller ID to include the DID, which will allow replying via non-default DID
                    $from = sprintf("%s+%s", $data['to'], $data['from']);
                }
                else
                {
                    $from = $data['from'];
                }

                $body = trim($data['message'], '"');

                // get contacts
                $result = $astman->send_request('Getvar', array('Variable' => "PJSIP_DIAL_CONTACTS($device)"));
                $contacts = array();
                if (!empty($result['Value']))
                {
                    $contacts = explode('&', $result['Value']);
                }

                if (!empty($contacts))
                {
                    foreach ($contacts as $contact) // message all registered
                    {
                        $to = sprintf("pjsip:%s", strstr($contact, 'sip'));
                        $result = $astman->MessageSend($to, $from, $body);
                        if ($result['Response'] == 'Error')
                        {
                            out(sprintf("Error sending message to %s: %s", $contact, $result['Message']));
                        }
                    }
                }
                else // no contacts registered - send email if enabled
                {
                    $smsconnector->notifyOfflineUser($uid, $data);
                }
            }
        }
    }
}