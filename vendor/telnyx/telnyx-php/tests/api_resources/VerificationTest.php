<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Verification
 */
final class VerificationTest extends \Telnyx\TestCase
{
    const TEST_RESOURCE_ID = '12ade33a-21c0-473b-b055-b3c836e1c292';
    const TEST_PHONE_NUMBER = '+13035551234';
    const TEST_VERIFICATION_CODE = '17686';

    /*
    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/verifications'
        );
        $resource = Verification::create([
            "verify_profile_id" => self::TEST_RESOURCE_ID,
            "phone_number" => self::TEST_PHONE_NUMBER,
            "type" => "sms"
        ]);
        $this->assertInstanceOf(\Telnyx\Verification::class, $resource);
    }
    */

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/verifications/' . urlencode(self::TEST_RESOURCE_ID)
        );
        $resource = Verification::retrieve(self::TEST_RESOURCE_ID);
        $this->assertInstanceOf(\Telnyx\Verification::class, $resource);
    }

    public function testRetrieveByPhoneNumber()
    {
        $this->expectsRequest(
            'get',
            '/v2/verifications/by_phone_number/' . urlencode(self::TEST_PHONE_NUMBER)
        );
        $resource = Verification::retrieve_by_phone_number(self::TEST_PHONE_NUMBER);
        $this->assertInstanceOf(\Telnyx\Collection::class, $resource);
        $this->assertInstanceOf(\Telnyx\Verification::class, $resource['data'][0]);
    }

    public function testSubmitVerification()
    {
        $this->expectsRequest(
            'post',
            '/v2/verifications/by_phone_number/' . urlencode(self::TEST_PHONE_NUMBER) . '/actions/verify'
        );
        $resource = Verification::submit_verification(self::TEST_PHONE_NUMBER, self::TEST_VERIFICATION_CODE);
        $this->assertInstanceOf(\Telnyx\TelnyxObject::class, $resource);
    }

}
