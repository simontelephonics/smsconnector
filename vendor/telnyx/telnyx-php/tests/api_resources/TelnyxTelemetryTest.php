<?php

namespace Telnyx;

/**
 * @internal
 * @coversNothing
 */
final class TelnyxTelemetryTest extends \Telnyx\TestCase
{
    const TEST_API_KEY = 'KEY123';
    const TEST_RESOURCE_ID = 'acct_123';
    const TEST_EXTERNALACCOUNT_ID = 'ba_123';
    const TEST_PERSON_ID = 'person_123';

    const FAKE_VALID_RESPONSE = '{
      "data": [],
      "record_type": "list",
      "meta": [],
      "url": "/v2/phone_numbers"
    }';

    protected function setUp()
    {
        // clear static telemetry data
        ApiRequestor::resetTelemetry();
    }

    public function testNoTelemetrySentIfNotEnabled()
    {
        Telnyx::setApiKey(self::TEST_API_KEY);
        $requestheaders = null;

        $stub = $this
            ->getMockBuilder('HttpClient\\ClientInterface')
            ->setMethods(['request'])
            ->getMock()
        ;

        $stub->expects(static::any())
            ->method('request')
            ->with(
                static::anything(),
                static::anything(),
                static::callback(function ($headers) use (&$requestheaders) {
                    foreach ($headers as $index => $header) {
                        // capture the requested headers and format back to into an assoc array
                        $components = \explode(': ', $header, 2);
                        $requestheaders[$components[0]] = $components[1];
                    }

                    return true;
                }),
                static::anything(),
                static::anything()
            )->willReturn([self::FAKE_VALID_RESPONSE, 200, ['request-id' => '123']]);

        ApiRequestor::setHttpClient($stub);

        // make one request to capture its result
        PhoneNumber::all();
        static::assertArrayNotHasKey('X-Telnyx-Client-Telemetry', $requestheaders);

        // make another request and verify telemetry isn't sent
        PhoneNumber::all();
        static::assertArrayNotHasKey('X-Telnyx-Client-Telemetry', $requestheaders);

        ApiRequestor::setHttpClient(null);
    }

    public function testTelemetrySetIfEnabled()
    {
        Telnyx::setApiKey(self::TEST_API_KEY);
        Telnyx::setEnableTelemetry(true);

        $requestheaders = null;

        $stub = $this
            ->getMockBuilder('HttpClient\\ClientInterface')
            ->setMethods(['request'])
            ->getMock()
        ;

        $stub->expects(static::any())
            ->method('request')
            ->with(
                static::anything(),
                static::anything(),
                static::callback(function ($headers) use (&$requestheaders) {
                    // capture the requested headers and format back to into an assoc array
                    foreach ($headers as $index => $header) {
                        $components = \explode(': ', $header, 2);
                        $requestheaders[$components[0]] = $components[1];
                    }

                    return true;
                }),
                static::anything(),
                static::anything()
            )->willReturn([self::FAKE_VALID_RESPONSE, 200, ['request-id' => ['req_123']]]);

        ApiRequestor::setHttpClient($stub);

        // make one request to capture its result
        PhoneNumber::all();
        static::assertArrayNotHasKey('X-Telnyx-Client-Telemetry', $requestheaders);

        // make another request to send the previous
        PhoneNumber::all();
        static::assertArrayHasKey('X-Telnyx-Client-Telemetry', $requestheaders);

        $data = \json_decode($requestheaders['X-Telnyx-Client-Telemetry'], true);
        static::assertNotNull($data['last_request_metrics']['request_duration_ms']);

        ApiRequestor::setHttpClient(null);
    }
}
