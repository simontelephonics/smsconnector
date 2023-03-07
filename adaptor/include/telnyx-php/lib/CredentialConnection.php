<?php

namespace Telnyx;

/**
 * Class CredentialConnection
 *
 * @package Telnyx
 */
class CredentialConnection extends ApiResource
{
    const OBJECT_NAME = "credential_connection";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

}
