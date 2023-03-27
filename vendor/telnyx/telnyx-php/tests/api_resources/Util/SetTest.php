<?php

namespace Telnyx\Util;

/**
 * @internal
 * @covers \Telnyx\Util\Set
 */
final class SetTest extends \Telnyx\TestCase
{
    public function testSet()
    {
        $data = ['a', 'b', 'c', 'd'];
        $result = new \Telnyx\Util\Set($data);
        static::assertSame(true, $result->includes('a'));
        static::assertSame(true, $result->includes('b'));
        static::assertSame(true, $result->includes('c'));
        static::assertSame(true, $result->includes('d'));
        static::assertSame(false, $result->includes('e'));

        $iterator = $result->getIterator();
        static::assertInstanceOf('ArrayIterator', $iterator);
    }
}
