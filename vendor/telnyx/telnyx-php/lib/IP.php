<?php

namespace Telnyx;

/**
 * Class IP
 *
 * @package Telnyx
 */
class IP extends ApiResource
{
    const OBJECT_NAME = "ip";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    
}
