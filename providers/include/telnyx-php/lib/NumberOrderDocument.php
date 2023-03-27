<?php

namespace Telnyx;

/**
 * Class NumberOrderDocument
 *
 * @package Telnyx
 */
class NumberOrderDocument extends ApiResource
{

    const OBJECT_NAME = "number_order_document";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

}
