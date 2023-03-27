<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\NumberOrder
 */
final class NumberOrderTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = "12ade33a-21c0-473b-b055-b3c836e1c292";

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_orders'
        );
        $resources = NumberOrder::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\NumberOrder::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_orders/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = NumberOrder::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\NumberOrder::class, $resource);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/number_orders'
        );
        $resource = NumberOrder::create([
            'phone_numbers' => [
                ['phone_number' => '+12223334444', 'regulatory_requirements' => []]
            ],
            'customer_reference' => 'MY REF 001',
            'connection_id' => '442191469269222625',
            'messaging_profile_id' => '730911e3-8488-40a8-a818-dc0a5df8bc03',
        ]);
        $this->assertInstanceOf(\Telnyx\NumberOrder::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/number_orders/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = NumberOrder::update(self::TEST_RESOURCE_ID, [
            "customer_reference" => "test",
        ]);
        $this->assertInstanceOf(\Telnyx\NumberOrder::class, $resource);
    }
}
