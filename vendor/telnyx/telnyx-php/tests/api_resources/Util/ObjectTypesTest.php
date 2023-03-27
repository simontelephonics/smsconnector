<?php

namespace Telnyx\Util;

/**
 * @internal
 * @covers \Telnyx\Util\ObjectTypes
 */
final class ObjectTypesTest extends \PHPUnit\Framework\TestCase
{

    public function testMapping()
    {
        static::assertSame(\Telnyx\Util\ObjectTypes::mapping['list'], \Telnyx\Collection::class);
        static::assertSame(\Telnyx\Util\ObjectTypes::mapping['messaging_settings'], \Telnyx\PhoneNumber\Messaging::class);
    }
}
