<?php

namespace Telnyx;

/**
 * Class FaxApplication
 *
 * @package Telnyx
 */
class FaxApplication extends ApiResource
{
    const OBJECT_NAME = "fax_application";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Update;
    use ApiOperations\Retrieve;

}
