<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\NumberReservation
 */
final class NumberReservationTest extends \Telnyx\TestCase
{
    const NUMBER_RESERVATION_ID = "f7964e2b-a9f9-4eb6-ab16-e570ffc4bc83";

    public function testIsListable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_reservations'
        );
        $resources = NumberReservation::all();
        $this->assertInstanceOf(\Telnyx\Collection::class, $resources);
        $this->assertInstanceOf(\Telnyx\NumberReservation::class, $resources['data'][0]);
    }

    public function testIsCreatable()
    {
        $this->expectsRequest(
            'post',
            '/v2/number_reservations'
        );
        $resource = \Telnyx\NumberReservation::create([
            "phone_number" => "+18665552368"
        ]);
        $this->assertInstanceOf(\Telnyx\NumberReservation::class, $resource);
    }

    public function testIsRetrievable()
    {
        $this->expectsRequest(
            'get',
            '/v2/number_reservations/' . urlencode(self::NUMBER_RESERVATION_ID)
        );
        $resource = \Telnyx\NumberReservation::retrieve(self::NUMBER_RESERVATION_ID);
        $this->assertInstanceOf(\Telnyx\NumberReservation::class, $resource);
    }

    public function testActionsExtend()
    {
        $number_reservation = \Telnyx\NumberReservation::retrieve(self::NUMBER_RESERVATION_ID);
        $this->expectsRequest(
            'post',
            '/v2/number_reservations/' . urlencode(self::NUMBER_RESERVATION_ID) . '/actions/extend'
        );
        $resource = $number_reservation->actions_extend();
        $this->assertInstanceOf(\Telnyx\NumberReservation::class, $resource);
    }

}
