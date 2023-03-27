<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\Update
 */

class DummyUpdate extends ApiResource
{
    const OBJECT_NAME = 'phone_number';

    use \Telnyx\ApiOperations\Update;
}

final class UpdateTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testTraitUpdate()
    {
        $result = DummyUpdate::update(self::TEST_RESOURCE_ID, ['customer_reference'=>'MY REF 001']);
        $this->assertInstanceOf(\Telnyx\PhoneNumber::class, $result);
        $this->assertNotNull($result['connection_id']);
        $this->assertNotNull($result['id']);
    }

    public function testTraitSave()
    {
        $class = new DummyUpdate(self::TEST_RESOURCE_ID);
        $class->customer_reference = 'MY REF 001';
        $result = $class->save();
        $this->assertNotNull($result['connection_id']);
        $this->assertNotNull($result['id']);
    }
}
