<?php

// Telnyx singleton
require __DIR__ . '/lib/Telnyx.php';

// Utilities
require __DIR__ . '/lib/Util/AutoPagingIterator.php';
require __DIR__ . '/lib/Util/CaseInsensitiveArray.php';
require __DIR__ . '/lib/Util/LoggerInterface.php';
require __DIR__ . '/lib/Util/DefaultLogger.php';
require __DIR__ . '/lib/Util/RandomGenerator.php';
require __DIR__ . '/lib/Util/RequestOptions.php';
require __DIR__ . '/lib/Util/Set.php';
require __DIR__ . '/lib/Util/Util.php';
require __DIR__ . '/lib/Util/ObjectTypes.php';

// HttpClient
require __DIR__ . '/lib/HttpClient/ClientInterface.php';
require __DIR__ . '/lib/HttpClient/CurlClient.php';

// Exceptions
require __DIR__ . '/lib/Exception/ExceptionInterface.php';
require __DIR__ . '/lib/Exception/ApiErrorException.php';
require __DIR__ . '/lib/Exception/ApiConnectionException.php';
require __DIR__ . '/lib/Exception/AuthenticationException.php';
require __DIR__ . '/lib/Exception/BadMethodCallException.php';
require __DIR__ . '/lib/Exception/InvalidArgumentException.php';
require __DIR__ . '/lib/Exception/InvalidRequestException.php';
require __DIR__ . '/lib/Exception/PermissionException.php';
require __DIR__ . '/lib/Exception/RateLimitException.php';
require __DIR__ . '/lib/Exception/SignatureVerificationException.php';
require __DIR__ . '/lib/Exception/UnexpectedValueException.php';
require __DIR__ . '/lib/Exception/UnknownApiErrorException.php';

// API operations
require __DIR__ . '/lib/ApiOperations/All.php';
require __DIR__ . '/lib/ApiOperations/Create.php';
require __DIR__ . '/lib/ApiOperations/Delete.php';
require __DIR__ . '/lib/ApiOperations/NestedResource.php';
require __DIR__ . '/lib/ApiOperations/Request.php';
require __DIR__ . '/lib/ApiOperations/Retrieve.php';
require __DIR__ . '/lib/ApiOperations/Update.php';

// Plumbing
require __DIR__ . '/lib/ApiResponse.php';
require __DIR__ . '/lib/RequestTelemetry.php';
require __DIR__ . '/lib/TelnyxObject.php';
require __DIR__ . '/lib/ApiRequestor.php';
require __DIR__ . '/lib/ApiResource.php';
require __DIR__ . '/lib/Collection.php';
require __DIR__ . '/lib/ErrorObject.php';
require __DIR__ . '/lib/Event.php';

// Telnyx API: Numbers
require __DIR__ . '/lib/AvailablePhoneNumber.php';
require __DIR__ . '/lib/NumberOrder.php';
require __DIR__ . '/lib/NumberReservation.php';
require __DIR__ . '/lib/RegulatoryRequirement.php';
require __DIR__ . '/lib/NumberOrderDocument.php';
require __DIR__ . '/lib/PhoneNumber.php';
require __DIR__ . '/lib/PhoneNumber/Voice.php';
require __DIR__ . '/lib/PhoneNumber/Messaging.php';
require __DIR__ . '/lib/Call.php';
require __DIR__ . '/lib/Conference.php';
require __DIR__ . '/lib/CallControlApplication.php';
require __DIR__ . '/lib/NumberLookup.php';

// Telnyx API: Messaging
require __DIR__ . '/lib/Message.php';
require __DIR__ . '/lib/MessagingProfile.php';
require __DIR__ . '/lib/MessagingPhoneNumber.php';
require __DIR__ . '/lib/AlphanumericSenderID.php';
require __DIR__ . '/lib/ShortCode.php';
require __DIR__ . '/lib/OutboundVoiceProfile.php';
require __DIR__ . '/lib/MessagingHostedNumberOrder.php';

// Telnyx API: Misc
require __DIR__ . '/lib/Address.php';
require __DIR__ . '/lib/BillingGroup.php';
require __DIR__ . '/lib/InboundChannel.php';
require __DIR__ . '/lib/SimCard.php';
require __DIR__ . '/lib/Portout.php';
require __DIR__ . '/lib/OtaUpdate.php';
require __DIR__ . '/lib/MobileOperatorNetwork.php';
require __DIR__ . '/lib/Balance.php';
require __DIR__ . '/lib/VerifyProfile.php';
require __DIR__ . '/lib/Verification.php';
require __DIR__ . '/lib/VerifyVerification.php';

// Telnyx API: Connections
require __DIR__ . '/lib/Connection.php';
require __DIR__ . '/lib/IPConnection.php';
require __DIR__ . '/lib/CredentialConnection.php';
require __DIR__ . '/lib/IP.php';
require __DIR__ . '/lib/FQDNConnection.php';
require __DIR__ . '/lib/FQDN.php';
require __DIR__ . '/lib/Fax.php';
require __DIR__ . '/lib/FaxApplication.php';

// Webhooks
require __DIR__ . '/lib/Webhook.php';
require __DIR__ . '/lib/WebhookSignature.php';


require __DIR__ . '/lib/TelephonyCredential.php';
require __DIR__ . '/lib/PortingOrder.php';
require __DIR__ . '/lib/PortingPhoneNumber.php';
