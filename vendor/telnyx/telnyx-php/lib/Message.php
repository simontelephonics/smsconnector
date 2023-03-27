<?php

namespace Telnyx;

/**
 * Class Message
 *
 * @package Telnyx
 */
class Message extends ApiResource
{
    const OBJECT_NAME = "message";

    use ApiOperations\Create;
    use ApiOperations\Retrieve;
}
