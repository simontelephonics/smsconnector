<?php

namespace Telnyx;

abstract class Webhook
{
    const DEFAULT_TOLERANCE = 300;

    /**
     * Alias for constructEvent().
     * Returns an Event instance using the current HTTPS request. Throws an
     * Exception\UnexpectedValueException if the payload is not valid JSON, and
     * an Exception\SignatureVerificationException if the signature
     * verification fails for any reason.
     *
     * @param string $public_key secret used to generate the signature
     * @param int $tolerance maximum difference allowed between the header's
     *  timestamp and the current time
     *
     * @throws Exception\UnexpectedValueException if the payload is not valid JSON,
     * @throws Exception\SignatureVerificationException if the verification fails
     *
     * @return Event the Event instance
     */
    public static function constructFromRequest($public_key = '', $tolerance = self::DEFAULT_TOLERANCE)
    {
        if (!isset($_SERVER['HTTP_TELNYX_SIGNATURE_ED25519']) || !isset($_SERVER['HTTP_TELNYX_TIMESTAMP'])) {
            throw Exception\SignatureVerificationException::factory(
                'Unable to extract timestamp and signatures from header'
            );
        }

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_TELNYX_SIGNATURE_ED25519'];
        $timestamp_header = $_SERVER['HTTP_TELNYX_TIMESTAMP'];

        return \Telnyx\Webhook::constructEvent(
            $payload, $sig_header, $timestamp_header, $public_key, $tolerance
        );
    }

    /**
     * Returns an Event instance using the provided JSON payload. Throws an
     * Exception\UnexpectedValueException if the payload is not valid JSON, and
     * an Exception\SignatureVerificationException if the signature
     * verification fails for any reason.
     *
     * @param string $payload the payload sent by Telnyx
     * @param string $signature_header the contents of the signature header sent by Telnyx
     * @param string $timestamp the timestamp from the header sent by Telnyx
     * @param string $public_key secret used to generate the signature
     * @param int $tolerance maximum difference allowed between the header's
     *  timestamp and the current time
     *
     * @throws Exception\UnexpectedValueException if the payload is not valid JSON,
     * @throws Exception\SignatureVerificationException if the verification fails
     *
     * @return Event the Event instance
     */
    public static function constructEvent($payload, $signature_header, $timestamp, $public_key = '', $tolerance = self::DEFAULT_TOLERANCE)
    {
        WebhookSignature::verifyHeader($payload, $signature_header, $timestamp, $public_key, $tolerance);

        $data = \json_decode($payload, true);
        $jsonError = \json_last_error();
        if (null === $data && \JSON_ERROR_NONE !== $jsonError) {
            $msg = "Invalid payload: {$payload} "
              . "(json_last_error() was {$jsonError})";

            throw new Exception\UnexpectedValueException($msg);
        }

        return Event::constructFrom($data);
    }
}
