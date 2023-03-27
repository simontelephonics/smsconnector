<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\PortingOrder
 */
final class PortingOrderTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = 'f1486bae-f067-460c-ad43-73a92848f902';
    
    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/porting_orders'
        );
        $resources = PortingOrder::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/porting_orders'
        );
        $resources = PortingOrder::create(["phone_numbers" => ["+13035550000","+13035550001","+13035550002"]]);
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/porting_orders/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = PortingOrder::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/porting_orders/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = PortingOrder::update(self::TEST_RESOURCE_ID, ["customer_reference" => "string"]);
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = PortingOrder::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/porting_orders/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resource);
    }

    /*
    Note: Currently in beta
    public function testLoaTemplate()
    {
        $PortingOrder = PortingOrder::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'get',
            '/v2/porting_orders/' . urlencode(self::TEST_RESOURCE_ID) . '/loa_template'
        );
        $resources = $PortingOrder->loa_template();
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resources);
    }
    */

    public function testConfirm()
    {
        $PortingOrder = PortingOrder::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/porting_orders/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/confirm'
        );
        $resources = $PortingOrder->confirm();
        $this->assertInstanceOf(\Telnyx\PortingOrder::class, $resources);
    }

}
