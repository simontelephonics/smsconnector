<?php

namespace Telnyx;

/**
 * Class BillingGroup
 *
 * @package Telnyx
 */
class BillingGroup extends ApiResource
{

    const OBJECT_NAME = "billing_group";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

}
