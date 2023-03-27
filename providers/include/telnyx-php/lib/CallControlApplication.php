<?php

namespace Telnyx;

/**
 * Class CallControlApplication
 *
 * @package Telnyx
 */
class CallControlApplication extends ApiResource
{
    const OBJECT_NAME = "call_control_application";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
