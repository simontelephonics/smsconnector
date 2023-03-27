<?php
require_once(__DIR__ . '/../init.php');

// Please fetch the public key from: https://portal.telnyx.com/#/app/account/public-key
\Telnyx\Telnyx::setPublicKey('######');

$webhook_event = null;

try {
    
    // Validate the webhook against the public key and retrieve the $webhook_event object
    $webhook_event = \Telnyx\Webhook::constructFromRequest();


} catch(\UnexpectedValueException $e) { // Invalid payload

    // Output error message
    echo $e;

    // Send status code to signal that the webhook was NOT successfully received
    http_response_code(400);
    exit();
} catch(\Telnyx\Exception\SignatureVerificationException $e) { // Invalid signature

    // Output error message
    echo $e;
    
    // Send status code to signal that the webhook was NOT successfully received
    http_response_code(400);
    exit();
}

// Now you can work with the $webhook_event object
print_r($webhook_event);

// Send status code to signal that the webhook was successfully received
http_response_code(200);