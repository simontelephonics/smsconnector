<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiOperations\Delete
 */

class DummyDelete extends ApiResource
{
    const OBJECT_NAME = 'ip_connection';

    use \Telnyx\ApiOperations\Delete;
}

final class DeleteTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testTraitDelete()
    {
        $class = new DummyDelete(self::TEST_RESOURCE_ID);
        $result = $class->delete();
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $result);
    }
    public function testTraitRemove()
    {
        $result = DummyDelete::remove(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $result);
    }
}
