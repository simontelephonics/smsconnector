<?php

namespace Telnyx\Exception;

/**
 * Implements properties and methods common to all (non-SPL) Telnyx exceptions.
 */
abstract class ApiErrorException extends \Exception implements ExceptionInterface
{
    protected $error;
    protected $httpBody;
    protected $httpHeaders;
    protected $httpStatus;
    protected $jsonBody;
    protected $requestId;
    protected $telnyxCode;

    /**
     * Creates a new API error exception.
     *
     * @param string $message The exception message.
     * @param int|null $httpStatus The HTTP status code.
     * @param string|null $httpBody The HTTP body as a string.
     * @param array|null $jsonBody The JSON deserialized body.
     * @param array|\Telnyx\Util\CaseInsensitiveArray|null $httpHeaders The HTTP headers array.
     * @param string|null $telnyxCode The Telnyx error code.
     *
     * @return static
     */
    public static function factory(
        $message,
        $httpStatus = null,
        $httpBody = null,
        $jsonBody = null,
        $httpHeaders = null,
        $telnyxCode = null
    ) {
        $instance = new static($message);
        $instance->setHttpStatus($httpStatus);
        $instance->setHttpBody($httpBody);
        $instance->setJsonBody($jsonBody);
        $instance->setHttpHeaders($httpHeaders);
        $instance->setTelnyxCode($telnyxCode);
        $instance->setRequestId(null);
        if ($httpHeaders && isset($httpHeaders['Request-Id'])) {
            $instance->setRequestId($httpHeaders['Request-Id']);
        }

        $instance->setError($instance->constructErrorObject());

        return $instance;
    }

    /**
     * Gets the Telnyx error object.
     *
     * @return \Telnyx\ErrorObject|null
     */
    public function getError()
    {
        return $this->errors;
    }

    /**
     * Sets the Telnyx error object.
     *
     * @param \Telnyx\ErrorObject|null $error
     */
    public function setError($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Gets the HTTP body as a string.
     *
     * @return string|null
     */
    public function getHttpBody()
    {
        return $this->httpBody;
    }

    /**
     * Sets the HTTP body as a string.
     *
     * @param string|null $httpBody
     */
    public function setHttpBody($httpBody)
    {
        $this->httpBody = $httpBody;
    }

    /**
     * Gets the HTTP headers array.
     *
     * @return array|\Telnyx\Util\CaseInsensitiveArray|null
     */
    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    /**
     * Sets the HTTP headers array.
     *
     * @param array|\Telnyx\Util\CaseInsensitiveArray|null $httpHeaders
     */
    public function setHttpHeaders($httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int|null
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * Sets the HTTP status code.
     *
     * @param int|null $httpStatus
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;
    }

    /**
     * Gets the JSON deserialized body.
     *
     * @return array|null
     */
    public function getJsonBody()
    {
        return $this->jsonBody;
    }

    /**
     * Sets the JSON deserialized body.
     *
     * @param array|null $jsonBody
     */
    public function setJsonBody($jsonBody)
    {
        $this->jsonBody = $jsonBody;
    }

    /**
     * Gets the Telnyx request ID.
     *
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Sets the Telnyx request ID.
     *
     * @param string|null $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Gets the Telnyx error code.
     *
     * Cf. the `CODE_*` constants on {@see \Telnyx\ErrorObject} for possible
     * values.
     *
     * @return string|null
     */
    public function getTelnyxCode()
    {
        return $this->telnyxCode;
    }

    /**
     * Sets the Telnyx error code.
     *
     * @param string|null $telnyxCode
     */
    public function setTelnyxCode($telnyxCode)
    {
        $this->telnyxCode = $telnyxCode;
    }

    /**
     * Returns the string representation of the exception.
     *
     * @return string
     */
    public function __toString()
    {
        $statusStr = ($this->getHttpStatus() == null) ? "" : "(HTTP {$this->getHttpStatus()}) ";
        $idStr = ($this->getRequestId() == null) ? "" : "(Request {$this->getRequestId()}) ";
        $codeStr = ($this->getTelnyxCode() == null) ? "" : "(Code {$this->getTelnyxCode()}) ";
        return "Telnyx API Exception {$statusStr}{$codeStr}{$idStr}{$this->getMessage()}";
    }

    protected function constructErrorObject()
    {
        if (is_null($this->jsonBody) || !array_key_exists('errors', $this->jsonBody)) {
            return null;
        }

        return \Telnyx\ErrorObject::constructFrom($this->jsonBody['errors'][0]);
    }
}
