<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\BillingGroup
 */
final class BillingGroupTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/billing_groups'
        );
        $resources = BillingGroup::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\BillingGroup::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/billing_groups'
        );
        $resource = BillingGroup::create(["name" => "My billing group name"]);
        $this->assertInstanceOf(\Telnyx\BillingGroup::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = BillingGroup::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/billing_groups/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\BillingGroup::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/billing_groups/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = BillingGroup::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\BillingGroup::class, $resource);
    }


    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/billing_groups/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = BillingGroup::update(self::TEST_RESOURCE_ID, [
            "name" => "My updated billing group name",
        ]);
        $this->assertInstanceOf(\Telnyx\BillingGroup::class, $resource);
    }
}
