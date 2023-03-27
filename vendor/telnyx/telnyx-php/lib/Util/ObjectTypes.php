<?php

namespace Telnyx\Util;

class ObjectTypes
{
    /**
     * @var array Mapping from object types to resource classes
     */
    const mapping = [
        // data structures
        \Telnyx\Collection::OBJECT_NAME => \Telnyx\Collection::class,

        // Telnyx API: Numbers
        \Telnyx\AvailablePhoneNumber::OBJECT_NAME => \Telnyx\AvailablePhoneNumber::class,
        \Telnyx\NumberOrder::OBJECT_NAME => \Telnyx\NumberOrder::class,
        \Telnyx\NumberReservation::OBJECT_NAME => \Telnyx\NumberReservation::class,
        \Telnyx\RegulatoryRequirement::OBJECT_NAME => \Telnyx\RegulatoryRequirement::class,
        \Telnyx\NumberOrderDocument::OBJECT_NAME => \Telnyx\NumberOrderDocument::class,
        \Telnyx\PhoneNumber::OBJECT_NAME => \Telnyx\PhoneNumber::class,
        \Telnyx\PhoneNumber\Voice::OBJECT_NAME => \Telnyx\PhoneNumber\Voice::class,
        \Telnyx\PhoneNumber\Messaging::OBJECT_NAME => \Telnyx\PhoneNumber\Messaging::class,
        \Telnyx\Call::OBJECT_NAME => \Telnyx\Call::class,
        \Telnyx\Conference::OBJECT_NAME => \Telnyx\Conference::class,
        \Telnyx\CallControlApplication::OBJECT_NAME => \Telnyx\CallControlApplication::class,
        \Telnyx\NumberLookup::OBJECT_NAME => \Telnyx\NumberLookup::class,

        // Telnyx API: Messaging
        \Telnyx\Message::OBJECT_NAME => \Telnyx\Message::class,
        \Telnyx\MessagingProfile::OBJECT_NAME => \Telnyx\MessagingProfile::class,
        \Telnyx\MessagingPhoneNumber::OBJECT_NAME => \Telnyx\MessagingPhoneNumber::class,
        \Telnyx\AlphanumericSenderID::OBJECT_NAME => \Telnyx\AlphanumericSenderID::class,
        \Telnyx\ShortCode::OBJECT_NAME => \Telnyx\ShortCode::class,
        \Telnyx\OutboundVoiceProfile::OBJECT_NAME => \Telnyx\OutboundVoiceProfile::class,
        \Telnyx\MessagingHostedNumberOrder::OBJECT_NAME => \Telnyx\MessagingHostedNumberOrder::class,

        // Telnyx API: Misc
        \Telnyx\Address::OBJECT_NAME => \Telnyx\Address::class,
        \Telnyx\BillingGroup::OBJECT_NAME => \Telnyx\BillingGroup::class,
        \Telnyx\InboundChannel::OBJECT_NAME => \Telnyx\InboundChannel::class,
        \Telnyx\SimCard::OBJECT_NAME => \Telnyx\SimCard::class,
        \Telnyx\Portout::OBJECT_NAME => \Telnyx\Portout::class,
        \Telnyx\OtaUpdate::OBJECT_NAME => \Telnyx\OtaUpdate::class,
        \Telnyx\MobileOperatorNetwork::OBJECT_NAME => \Telnyx\MobileOperatorNetwork::class,
        \Telnyx\Balance::OBJECT_NAME => \Telnyx\Balance::class,
        \Telnyx\VerifyProfile::OBJECT_NAME => \Telnyx\VerifyProfile::class,
        \Telnyx\Verification::OBJECT_NAME => \Telnyx\Verification::class,
        \Telnyx\VerifyVerification::OBJECT_NAME => \Telnyx\VerifyVerification::class,

        // Telnyx API: Connections
        \Telnyx\Connection::OBJECT_NAME => \Telnyx\Connection::class,
        \Telnyx\IPConnection::OBJECT_NAME => \Telnyx\IPConnection::class,
        \Telnyx\CredentialConnection::OBJECT_NAME => \Telnyx\CredentialConnection::class,
        \Telnyx\IP::OBJECT_NAME => \Telnyx\IP::class,
        \Telnyx\FQDNConnection::OBJECT_NAME => \Telnyx\FQDNConnection::class,
        \Telnyx\FQDN::OBJECT_NAME => \Telnyx\FQDN::class,
        \Telnyx\Fax::OBJECT_NAME => \Telnyx\Fax::class,
        \Telnyx\FaxApplication::OBJECT_NAME => \Telnyx\FaxApplication::class,

        
        \Telnyx\TelephonyCredential::OBJECT_NAME => \Telnyx\TelephonyCredential::class,
        \Telnyx\PortingOrder::OBJECT_NAME => \Telnyx\PortingOrder::class,
        \Telnyx\PortingPhoneNumber::OBJECT_NAME => \Telnyx\PortingPhoneNumber::class,
    ];
}
