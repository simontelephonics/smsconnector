<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\Retrieve
 */

class DummyRetrieve extends ApiResource
{
    const OBJECT_NAME = 'phone_number';

    use \Telnyx\ApiOperations\Retrieve;
}
class DummyRetrieveWithId extends ApiResource
{
    const OBJECT_NAME = 'call';
    const OBJECT_ID = 'call_control_id';

    use \Telnyx\ApiOperations\Retrieve;
}

final class RetrieveTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testTrait()
    {
        $result = DummyRetrieve::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $result);
        $this->assertNotNull($result['id']);
    }
    public function testTraitWithId()
    {
        $result = DummyRetrieveWithId::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $result);
        $this->assertNotNull($result['id']);
        $this->assertNotNull($result['call_control_id']);
    }
}
