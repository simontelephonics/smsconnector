<?php

namespace Telnyx;

/**
 * Class MessagingPhoneNumber
 *
 * @package Telnyx
 */
class MessagingPhoneNumber extends ApiResource
{
    const OBJECT_NAME = "messaging_phone_number";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
