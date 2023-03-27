<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\ApiResponse
 */
final class ApiResponseTest extends \Telnyx\TestCase
{
    public function testConstruct()
    {
        $obj = [
            'body' => 'body',
            'code' => 'code',
            'headers' => 'header',
            'json' => 'json'
        ];

        $class = new \Telnyx\ApiResponse($obj['body'], $obj['code'], $obj['headers'], $obj['json']);
        
        static::assertSame($obj['body'], $class->body);
        static::assertSame($obj['code'], $class->code);
        static::assertSame($obj['headers'], $class->headers);
        static::assertSame($obj['json'], $class->json);
    }
}
