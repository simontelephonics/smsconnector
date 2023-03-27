<?php

namespace Telnyx;

/**
 * Class ErrorObject.
 *
 * @property string $code - an application-specific error code, expressed as a stringified 5-digit integer [e.g. "10001"].
 * @property string $title - a short, human-readable summary of the problem.
 * @property string $detail - a human-readable explanation specific to this occurrence of the problem.
 * @property string $source - an object containing references to the source of the error, optionally including any of the following members:
 * @property string $pointer - a JSON Pointer [RFC6901] to the associated entity in the request document e.g. "/" for a primary object, or "/title" for a specific attribute.
 * @property string $parameter - a string indicating which URI query parameter caused the error.
 * @property string $meta - a meta object containing non-standard meta-information about the error. This will likely be a URL to the relevant documentation.
 */
class ErrorObject extends TelnyxObject
{
    /**
     * Possible string representations of an error's code.
     *
     * @see https://developers.telnyx.com/docs/v2/development/api-guide/errors
     */

    /**
     * Refreshes this object using the provided values.
     *
     * @param array $values
     * @param null|array|string|Util\RequestOptions $opts
     * @param bool $partial defaults to false
     */
    public function refreshFrom($values, $opts, $partial = false)
    {
        // Unlike most other API resources, the API will omit attributes in
        // error objects when they have a null value. We manually set default
        // values here to facilitate generic error handling.
        $values = \array_merge([
            'code' => null,
            'title' => null,
            'detail' => null,
            'source' => null,
            'pointer' => null,
            'parameter' => null,
            'meta' => null,
        ], $values);

        parent::refreshFrom($values, $opts, $partial);
    }
}
