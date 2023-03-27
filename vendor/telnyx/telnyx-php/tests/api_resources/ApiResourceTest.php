<?php

namespace Telnyx;

class DummyApiResource extends ApiResource
{
    const OBJECT_NAME = 'phone_number';

    // For testing nested resources
    public static function getSavedNestedResources()
    {
        static $savedNestedResources = null;
        if (null === $savedNestedResources) {
            $savedNestedResources = new Util\Set([
                'source',
            ]);
        }

        return $savedNestedResources;
    }
}

/**
 * @internal
 * @covers \Telnyx\ApiResource
 */
class ApiResourceTest extends \Telnyx\TestCase
{
    public function testGetSavedNestedResources()
    {
        $result = ApiResource::getSavedNestedResources();

        static::assertInstanceOf(\Telnyx\Util\Set::class, $result);
    }
    
    public function testSet()
    {
        $class = new DummyApiResource();
        $class->abc = '123';
        $class->source = new PhoneNumber();

        static::assertSame('123', $class->abc);
        static::assertInstanceOf(PhoneNumber::class, $class->source);
        static::assertTrue($class->source->saveWithParent);
        static::assertFalse($class->saveWithParent);
    }
    
    public function testResourceUrlNullId()
    {
        $class = new PhoneNumber();
        try {
            $url = $class->resourceUrl(null);
            static::fail('Did not raise error');
        } catch (\Telnyx\Exception\UnexpectedValueException $e) {
            static::assertSame(
                'Could not determine which URL to request: Telnyx\PhoneNumber instance has invalid ID: ',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            static::fail('Unexpected exception: ' . \get_class($e));
        }
    }
}
