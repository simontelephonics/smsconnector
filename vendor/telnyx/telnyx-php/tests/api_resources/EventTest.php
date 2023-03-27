<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Event
 */
final class EventTest extends \Telnyx\TestCase
{
    public function testEvent()
    {
        $resource = new Event();
        $this->assertInstanceOf(\Telnyx\Event::class, $resource);
    }
}
