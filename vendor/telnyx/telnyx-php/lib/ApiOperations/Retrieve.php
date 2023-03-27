<?php

namespace Telnyx\ApiOperations;

/**
 * Trait for retrievable resources. Adds a `retrieve()` static method to the
 * class.
 *
 * This trait should only be applied to classes that derive from TelnyxObject.
 */
trait Retrieve
{
    /**
     * @param array|string $id The ID of the API resource to retrieve,
     *     or an options array containing an `id` key.
     * @param array|string|null $opts
     *
     * @return \Telnyx\TelnyxObject
     */
    public static function retrieve($id, $opts = null)
    {
        $opts = \Telnyx\Util\RequestOptions::parse($opts);
        $instance = new static($id, $opts);
        $instance->refresh();

        // If 'id' is called something else like 'call_control_id'
        $class = get_class($instance);
        if (defined($class . '::OBJECT_ID')) {
            $instance->reassignId(static::OBJECT_ID);
        }

        return $instance;
    }
}
