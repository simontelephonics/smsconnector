<?php

namespace Telnyx;

/**
 * Class VerifyProfile
 *
 * @package Telnyx
 */
class VerifyProfile extends ApiResource
{
    const OBJECT_NAME = "verify_profile";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
    use ApiOperations\Delete;
}