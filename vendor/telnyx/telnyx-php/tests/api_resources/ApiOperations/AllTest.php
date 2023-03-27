<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\All
 */

class DummyAll extends ApiResource
{
    const OBJECT_NAME = 'phone_number';

    use \Telnyx\ApiOperations\All;
}
class DummyInvalidAll extends ApiResource
{
    const OBJECT_NAME = 'balance';

    use \Telnyx\ApiOperations\All;

    public static function classUrl() {
        return '/v2/balance';
    }
}

final class AllTest extends \Telnyx\TestCase
{
    public function testTrait()
    {
        $result = DummyAll::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $result);
    }
    public function testTraitInvalid()
    {
        try {
            $result = DummyInvalidAll::all();
            static::fail('Did not raise error');
        } catch (\Telnyx\Exception\UnexpectedValueException $e) {
            static::assertSame(
                'Expected type ' . \Telnyx\Collection::class . ', got "Telnyx\Balance" instead.',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            static::fail('Unexpected exception: ' . \get_class($e));
        }
    }
}
