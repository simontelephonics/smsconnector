<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\NumberOrderDocument
 */
final class NumberOrderDocumentTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_order_documents'
        );
        $resources = NumberOrderDocument::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\NumberOrderDocument::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/number_order_documents'
        );
        $resource = NumberOrderDocument::create(["country" => "US", "type" => "custom"]);
        $this->assertInstanceOf(\Telnyx\NumberOrderDocument::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_order_documents/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = NumberOrderDocument::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\NumberOrderDocument::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/number_order_documents/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = NumberOrderDocument::update(self::TEST_RESOURCE_ID, [
            "name" => "Test",
        ]);
        $this->assertInstanceOf(\Telnyx\NumberOrderDocument::class, $resource);
    }
}
