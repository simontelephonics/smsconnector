<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\RegulatoryRequirement
 */
final class RegulatoryRequirementTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/regulatory_requirements'
        );
        $resources = RegulatoryRequirement::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\RegulatoryRequirement::class, $resources['data'][0]);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/regulatory_requirements/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = RegulatoryRequirement::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\RegulatoryRequirement::class, $resource);
    }
}
