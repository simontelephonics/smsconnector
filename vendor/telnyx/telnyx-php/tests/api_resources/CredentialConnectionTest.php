<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\CredentialConnection
 */
final class CredentialConnectionTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '123';

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/credential_connections'
        );
        $resources = CredentialConnection::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\CredentialConnection::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/credential_connections'
        );
        $resource = CredentialConnection::create([
            'user_name' => 'test_user',
            'password' => 'iloveyou',
            'connection_name' => 'Test Connection',
            "anchorsite_override" => "Latency"
        ]);
        $this->assertInstanceOf(\Telnyx\CredentialConnection::class, $resource);
    }

    public function testIsDeletable()
    {
        $resource = CredentialConnection::retrieve(self::TEST_RESOURCE_ID);
        $this->expectsRequest(
            'delete',
            '/v2/credential_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource->delete();
        $this->assertInstanceOf(\Telnyx\CredentialConnection::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/credential_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = CredentialConnection::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\CredentialConnection::class, $resource);
    }

    public function testIsUpdatable()
    {
        $this->expectsRequest(
            'patch',
            '/v2/credential_connections/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = CredentialConnection::update(self::TEST_RESOURCE_ID, [
            "connection_name" => "Central BSD-1"
        ]);
        $this->assertInstanceOf(\Telnyx\CredentialConnection::class, $resource);
    }
}
