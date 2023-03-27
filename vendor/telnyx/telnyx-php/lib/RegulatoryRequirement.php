<?php

namespace Telnyx;

/**
 * Class RegulatoryRequirement
 *
 * @package Telnyx
 */
class RegulatoryRequirement extends ApiResource
{

    const OBJECT_NAME = "regulatory_requirement";

    use ApiOperations\All;
    use ApiOperations\Retrieve;

}
