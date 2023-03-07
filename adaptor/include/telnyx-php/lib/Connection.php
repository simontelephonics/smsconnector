<?php

namespace Telnyx;

/**
 * Class Connection
 *
 * @package Telnyx
 */
class Connection extends ApiResource
{
    const OBJECT_NAME = "connection";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
    
}
