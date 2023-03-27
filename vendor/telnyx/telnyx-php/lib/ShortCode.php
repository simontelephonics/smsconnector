<?php

namespace Telnyx;

/**
 * Class MessagingShortCode
 *
 * @package Telnyx
 */
class ShortCode extends ApiResource
{
    const OBJECT_NAME = "short_code";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
