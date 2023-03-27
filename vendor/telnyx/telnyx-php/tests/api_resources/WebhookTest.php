<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Webhook
 * @covers \Telnyx\WebhookSignature
 */
final class WebhookTest extends \Telnyx\TestCase
{
    const EVENT_PAYLOAD = '{
  "data": {
    "id": "evt_test_webhook",
    "record_type": "event"
  }
}';
    const SECRET = 'whsec_test_secret';

    private function generateKeypairSignature($timestamp = 0, $payload = self::EVENT_PAYLOAD) {
        $keypair = sodium_crypto_sign_keypair();

        $result['publicKey'] = sodium_crypto_sign_publicKey($keypair); // 32 bytes
        $result['secretKey'] = sodium_crypto_sign_secretKey($keypair); // 64 bytes
        $result['signature'] = sodium_crypto_sign_detached($timestamp . '|' . $payload, $result['secretKey']);

        return $result;
    }
    private function generateHeader($opts = [])
    {
        $timestamp = \array_key_exists('timestamp', $opts) ? $opts['timestamp'] : \time();
        $payload = \array_key_exists('payload', $opts) ? $opts['payload'] : self::EVENT_PAYLOAD;
        $secret = \array_key_exists('secret', $opts) ? $opts['secret'] : self::SECRET;
        $signature = \array_key_exists('signature', $opts) ? $opts['signature'] : null;
        if (null === $signature) {
            $signedPayload = "{$timestamp}.{$payload}";
            $signature = \hash_hmac('sha256', $signedPayload, $secret);
        }

        return "t={$timestamp},{$signature}";
    }

    public function testValidJsonAndHeader()
    {
        $timestamp = 0;
        $keypair = self::generateKeypairSignature($timestamp, self::EVENT_PAYLOAD);
        $event = Webhook::constructEvent(self::EVENT_PAYLOAD, base64_encode($keypair['signature']), $timestamp, base64_encode($keypair['publicKey']), 0);
        static::assertSame('evt_test_webhook', $event->data['id']);
    }

    public function testInvalidJson()
    {
        $this->expectException(\Telnyx\Exception\UnexpectedValueException::class);

        $payload = 'this is not valid JSON';
        $keypair = self::generateKeypairSignature(0, $payload);
        $event = Webhook::constructEvent($payload, base64_encode($keypair['signature']), 0, base64_encode($keypair['publicKey']), 0);
    }

    public function testValidJsonAndInvalidHeader()
    {
        $this->expectException(\Telnyx\Exception\SignatureVerificationException::class);

        $sigHeader = 'bad_header';
        Webhook::constructEvent(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
    }

    public function testMalformedHeader()
    {
        $this->expectException(\Telnyx\Exception\SignatureVerificationException::class);
        $this->expectExceptionMessage('Unable to extract timestamp and signatures from header');

        Webhook::constructFromRequest();
    }

    public function testTimestampTooOld()
    {
        $this->expectException(\Telnyx\Exception\SignatureVerificationException::class);
        $this->expectExceptionMessage('Timestamp outside the tolerance zone');

        $timestamp = \time() - 15;
        $keypair = self::generateKeypairSignature($timestamp, self::EVENT_PAYLOAD);
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, base64_encode($keypair['signature']), $timestamp, base64_encode($keypair['publicKey']), 10);
    }

    public function testTimestampTooRecent()
    {
        $this->expectException(\Telnyx\Exception\SignatureVerificationException::class);
        $this->expectExceptionMessage('Timestamp outside the tolerance zone');

        $timestamp = \time() + 15;
        $keypair = self::generateKeypairSignature($timestamp, self::EVENT_PAYLOAD);
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, base64_encode($keypair['signature']), $timestamp, base64_encode($keypair['publicKey']), 10);
    }

    public function testValidHeaderAndSignature()
    {
        $keypair = self::generateKeypairSignature();
        static::assertTrue(WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, base64_encode($keypair['signature']), 0, base64_encode($keypair['publicKey']), 0));
    }

    public function testTimestampOffButNoTolerance()
    {
        $timestamp = 12345;
        $keypair = self::generateKeypairSignature($timestamp, self::EVENT_PAYLOAD);
        static::assertTrue(WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, base64_encode($keypair['signature']), $timestamp, base64_encode($keypair['publicKey']), 0));
    }
}
