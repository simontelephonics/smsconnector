<?php
require_once(__DIR__ . '/../init.php');

// Please fetch the public key from: https://portal.telnyx.com/#/app/account/public-key
\Telnyx\Telnyx::setPublicKey('######');

// Validate the webhook against the public key and retrieve the event object
$webhook_event = \Telnyx\Webhook::constructFromRequest();

// Now you can work with the $webhook_event object
print_r($webhook_event);

// Send status code to signal that the webhook was successfully received
http_response_code(200);