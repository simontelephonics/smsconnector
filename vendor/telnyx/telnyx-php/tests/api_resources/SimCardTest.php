<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\SimCard
 */
final class SimCardTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '6a09cdc3-8948-47f0-aa62-74ac943d6c58';
    
    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/sim_cards'
        );
        $resources = SimCard::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = SimCard::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = SimCard::update(self::TEST_RESOURCE_ID, [
            "name" => "Test",
        ]);
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resource);
    }

    /*
    public function testActivate()
    {
        $simcard = SimCard::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/activate'
        );
        $resources = $simcard->activate();
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources);
    }

    public function testDeactivate()
    {
        $simcard = SimCard::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/deactivate'
        );
        $resources = $simcard->deactivate();
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources);
    }
    */

    public function testEnable()
    {
        $simcard = SimCard::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/enable'
        );
        $resources = $simcard->enable();
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources);
    }

    public function testDisable()
    {
        $simcard = SimCard::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'post',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/actions/disable'
        );
        $resources = $simcard->disable();
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources);
    }

    public function testRegister()
    {
        $this->expectsRequest(
            'post',
            '/v2/actions/register/sim_cards'
        );
        $resources = SimCard::register(["registration_codes" => ["1234567890, 123456332601"]]);
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\SimCard::class, $resources['data'][0]);
    }

    public function testDeleteNetworkPreferences()
    {
        $this->expectsRequest(
            'delete',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/network_preferences'
        );
        $resource = SimCard::delete_network_preferences(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testGetNetworkPreferences()
    {
        $this->expectsRequest(
            'get',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/network_preferences'
        );
        $resource = SimCard::get_network_preferences(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

    public function testSetNetworkPreferences()
    {
        $this->expectsRequest(
            'put',
            '/v2/sim_cards/' . urlencode(self::TEST_RESOURCE_ID) . '/network_preferences'
        );
        $resource = SimCard::set_network_preferences(self::TEST_RESOURCE_ID, [
            'mobile_operator_network_id' => '6a09cdc3-8948-47f0-aa62-74ac943d6c58'
        ]);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }
}
