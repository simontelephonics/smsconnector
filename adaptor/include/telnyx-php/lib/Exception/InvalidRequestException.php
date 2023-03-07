<?php

namespace Telnyx\Exception;

/**
 * InvalidRequestException is thrown when a request is initiated with invalid
 * parameters.
 *
 * @package Telnyx\Exception
 */
class InvalidRequestException extends ApiErrorException
{
    protected $telnyxParam;

    /**
     * Creates a new InvalidRequestException exception.
     *
     * @param string $message The exception message.
     * @param int|null $httpStatus The HTTP status code.
     * @param string|null $httpBody The HTTP body as a string.
     * @param array|null $jsonBody The JSON deserialized body.
     * @param array|\Telnyx\Util\CaseInsensitiveArray|null $httpHeaders The HTTP headers array.
     * @param string|null $telnyxCode The Telnyx error code.
     * @param string|null $telnyxParam The parameter related to the error.
     *
     * @return InvalidRequestException
     */
    public static function factory(
        $message,
        $httpStatus = null,
        $httpBody = null,
        $jsonBody = null,
        $httpHeaders = null,
        $telnyxCode = null,
        $telnyxParam = null
    ) {
        $instance = parent::factory($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders, $telnyxCode);
        $instance->setTelnyxParam($telnyxParam);

        return $instance;
    }

    /**
     * Gets the parameter related to the error.
     *
     * @return string|null
     */
    public function getTelnyxParam()
    {
        return $this->telnyxParam;
    }

    /**
     * Sets the parameter related to the error.
     *
     * @param string|null $telnyxParam
     */
    public function setTelnyxParam($telnyxParam)
    {
        $this->telnyxParam = $telnyxParam;
    }
}
