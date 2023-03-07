<?php

namespace Telnyx;

/**
 * Class IPConnection
 *
 * @package Telnyx
 */
class IPConnection extends ApiResource
{
    const OBJECT_NAME = "ip_connection";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    
}
