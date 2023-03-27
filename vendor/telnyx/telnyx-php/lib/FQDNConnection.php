<?php

namespace Telnyx;

/**
 * Class FQDNConnection
 *
 * @package Telnyx
 */
class FQDNConnection extends ApiResource
{
    const OBJECT_NAME = "fqdn_connection";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    
}
