<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\VerifyProfile
 */
final class VerifyProfileTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '12ade33a-21c0-473b-b055-b3c836e1c292';

    /*
    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/verify_profiles'
        );
        $resources = VerifyProfile::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\VerifyProfile::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/verify_profiles'
        );
        $resource = VerifyProfile::create([
            "name" => "Test Profile",
            "default_timeout_secs" => 300
        ]);
        $this->assertInstanceOf(\Telnyx\VerifyProfile::class, $resource);
    }
    */

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/verify_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = VerifyProfile::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\VerifyProfile::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = VerifyProfile::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/verify_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\VerifyProfile::class, $resource);
    }

    /*
    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/verify_profiles/' . urlencode(self::TEST_RESOURCE_ID)
        );

        $resource = VerifyProfile::update(self::TEST_RESOURCE_ID, [
            "name" => "Test Profile",
            "default_timeout_secs" => 300
        ]);
        $this->assertInstanceOf(\Telnyx\VerifyProfile::class, $resource);
    }
    */
}
