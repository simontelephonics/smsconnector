<?php

namespace Telnyx;

/**
 * Class FQDN
 *
 * @package Telnyx
 */
class FQDN extends ApiResource
{
    const OBJECT_NAME = "fqdn";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    
}
