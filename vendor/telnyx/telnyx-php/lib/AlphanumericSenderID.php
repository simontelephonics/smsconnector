<?php

namespace Telnyx;

/**
 * Class MessagingSenderID
 *
 * @package Telnyx
 */
class AlphanumericSenderID extends ApiResource
{
    const OBJECT_NAME = "alphanumeric_sender_id";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
