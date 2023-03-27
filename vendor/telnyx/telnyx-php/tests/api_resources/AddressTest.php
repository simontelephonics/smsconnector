<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Address
 */
final class AddressTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '1293384261075731499';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/addresses'
        );
        $resources = Address::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\Address::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/addresses'
        );
        $resource = Address::create([
            "first_name" => "Alfred",
            "last_name" => "Foster",
            "business_name" => "Company",
            "country_code" => "US",
            "locality" => "Chicago",
            "street_address" => "311 W Superior Street"
        ]);
        $this->assertInstanceOf(\Telnyx\Address::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = Address::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/addresses/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\Address::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/addresses/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Address::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Address::class, $resource);
    }
}
