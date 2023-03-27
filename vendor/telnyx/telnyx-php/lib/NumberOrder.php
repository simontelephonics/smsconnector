<?php

namespace Telnyx;

/**
 * Class NumberOrder
 *
 * @package Telnyx
 */
class NumberOrder extends ApiResource
{
    const OBJECT_NAME = "number_order";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
