<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\Request
 */

class DummyRequest extends ApiResource
{
    const OBJECT_NAME = 'phone_number';

    use \Telnyx\ApiOperations\Request;

    public static function fail() {
        DummyRequest::_validateParams('throw_error');
    }
}

final class RequestTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testTrait()
    {
        try {
            $result = DummyRequest::fail();
            static::fail('Did not raise error');
        } catch (\Telnyx\Exception\InvalidArgumentException $e) {

            $message = "You must pass an array as the first argument to Telnyx API "
               . "method calls.  (HINT: an example call to create a call "
               . "would be: \"Telnyx\\Call::create(['connection_id' => 'uuid', 'to'"
               . "=> '+18005550199', 'from' => '+18005550100'])\")";

            static::assertSame(
                $message,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            static::fail('Unexpected exception: ' . \get_class($e));
        }
    }
}
