<?php

namespace Telnyx\Exception;

/**
 * @internal
 * @covers \Telnyx\Exception\SignatureVerificationException
 */
final class SignatureVerificationExceptionTest extends \Telnyx\TestCase
{

    public function testGetters()
    {
        $e = SignatureVerificationException::factory('message', 'payload', 'sig_header');
        static::assertSame('message', $e->getMessage());
        static::assertSame('payload', $e->getHttpBody());
        static::assertSame('sig_header', $e->getSigHeader());
    }
}
