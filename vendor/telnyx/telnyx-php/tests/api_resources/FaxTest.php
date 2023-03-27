<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Fax
 */
final class FaxTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/faxes'
        );
        $resources = Fax::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\Fax::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/faxes'
        );
        $resource = Fax::create([
            "connection_id" => "234423",
            "media_url" => "http://assets.com/FAX.pdf",
            "to" => "+13127367276",
            "from" => "+13125790015"
        ]);
        $this->assertInstanceOf(\Telnyx\Fax::class, $resource);
    }

    /*
    public function testIsDeletable()
    {
        $resource = Fax::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/faxes/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\Fax::class, $resource);
    }
    */

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/faxes/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Fax::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Fax::class, $resource);
    }
}
