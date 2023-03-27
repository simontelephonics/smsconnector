<?php

namespace Telnyx\Exception;

/**
 * @internal
 * @covers \Telnyx\Exception\ApiErrorException
 */
final class ApiErrorExceptionTest extends \Telnyx\TestCase
{
    public function createFixture()
    {
        $mock = $this->getMockForAbstractClass(ApiErrorException::class);

        return $mock::factory(
            'message',
            200,

            // $httpBody
            '{"errors":[{"code":"some_code"}]}',

            // $jsonBody
            [
                "errors" => [
                    [
                        "code" => "some_code",
                        "title" => "some_title",
                        "detail" => "some_detail",
                        "source" => [
                            "pointer" => "/some_pointer"
                        ],
                        "meta" => [
                            "url" => "some_url"
                        ]
                    ]
                ]
            ],

            // $httpHeaders
            [
                'Some-Header' => 'Some Value',
                'Request-Id' => 'req_test',
            ],

            // $telnyxCode
            'some_code'
        );
        
    }

    public function testGetters()
    {
        $e = $this->createFixture();

        static::assertSame(200, $e->getHttpStatus());
        static::assertSame('{"errors":[{"code":"some_code"}]}', $e->getHttpBody());
        static::assertSame([
            "errors" => [
                [
                    "code" => "some_code",
                    "title" => "some_title",
                    "detail" => "some_detail",
                    "source" => [
                        "pointer" => "/some_pointer"
                    ],
                    "meta" => [
                        "url" => "some_url"
                    ]
                ]
            ]
        ], $e->getJsonBody());

        static::assertSame('Some Value', $e->getHttpHeaders()['Some-Header']);
        static::assertSame('some_code', $e->getTelnyxCode());
        static::assertNotNull($e->getError());
        static::assertSame('some_code', $e->getError()->code);
        static::assertSame('some_detail', $e->getError()->detail);
        static::assertSame('some_title', $e->getError()->title);
    }

    public function testToString()
    {
        $e = $this->createFixture();
        static::assertContains('(Request req_test)', (string) $e);
    }

    public function testNull()
    {
        $mock = $this->getMockForAbstractClass(ApiErrorException::class);

        $result = $mock::factory(
            'message',
            200,

            // $httpBody
            '{"errors":[{"code":"some_code"}]}',

            // $jsonBody
            null,

            // $httpHeaders
            [
                'Some-Header' => 'Some Value',
                'Request-Id' => 'req_test',
            ],

            // $telnyxCode
            'some_code'
        );

        static::assertNull($result->getError());
    }
}
