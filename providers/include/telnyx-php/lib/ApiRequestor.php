<?php

namespace Telnyx;

/**
 * Class ApiRequestor
 *
 * @package Telnyx
 */
class ApiRequestor
{
    /**
     * @var string|null
     */
    private $_apiKey;

    /**
     * @var string
     */
    private $_apiBase;

    /**
     * @var HttpClient\ClientInterface
     */
    private static $_httpClient;

    /**
     * @var RequestTelemetry
     */
    private static $requestTelemetry;

    /**
     * ApiRequestor constructor.
     *
     * @param string|null $apiKey
     * @param string|null $apiBase
     */
    public function __construct($apiKey = null, $apiBase = null)
    {
        $this->_apiKey = $apiKey;
        if (!$apiBase) {
            $apiBase = Telnyx::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    /**
     * Creates a telemetry json blob for use in 'X-Telnyx-Client-Telemetry' headers
     * @static
     *
     * @param RequestTelemetry $requestTelemetry
     * @return string
     */
    private static function _telemetryJson($requestTelemetry)
    {
        $payload = array(
            'last_request_metrics' => array(
                'request_id' => $requestTelemetry->requestId,
                'request_duration_ms' => $requestTelemetry->requestDuration,
        ));

        $result = json_encode($payload);
        if ($result != false) {
            return $result;
        } else {
            Telnyx::getLogger()->error("Serializing telemetry payload failed!");
            return "{}";
        }
    }

    /**
     * @static
     *
     * @param ApiResource|bool|array|mixed $d
     *
     * @return ApiResource|array|string|mixed
     */
    private static function _encodeObjects($d)
    {
        if ($d instanceof ApiResource) {
            return Util\Util::utf8($d->id);
        } elseif ($d === true) {
            return true;
        } elseif ($d === false) {
            return false;
        } elseif (is_array($d)) {
            $res = [];
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }
            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    /**
     * @param string     $method
     * @param string     $url
     * @param null|array $params
     * @param null|array $headers
     *
     * @throws Exception\ApiErrorException
     *
     * @return array tuple containing (ApiReponse, API key)
     */
    public function request($method, $url, $params = null, $headers = null)
    {
        $params = $params ?: [];
        $headers = $headers ?: [];
        list($rbody, $rcode, $rheaders, $myApiKey) = $this->_requestRaw($method, $url, $params, $headers);
        $json = $this->_interpretResponse($rbody, $rcode, $rheaders);
        $resp = new ApiResponse($rbody, $rcode, $rheaders, $json);

        return [$resp, $myApiKey];
    }

    /**
     * @param string $rbody a JSON string
     * @param int $rcode
     * @param array $rheaders
     * @param array $resp
     *
     * @throws Exception\UnexpectedValueException
     * @throws Exception\ApiErrorException
     */
    public function handleErrorResponse($rbody, $rcode, $rheaders, $resp)
    {
        if (!\is_array($resp) || !isset($resp['errors'])) {
            $msg = "Invalid response object from API: {$rbody} "
              . "(HTTP response code was {$rcode})";

            throw new Exception\UnexpectedValueException($msg);
        }

        $errorData = $resp['errors'];

        $error = null;
        if (!$error) {
            $error = self::_specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData);
        }

        throw $error;
    }

    /**
     * @static
     *
     * @param string $rbody
     * @param int    $rcode
     * @param array  $rheaders
     * @param array  $resp
     * @param array  $errorData
     *
     * @return Exception\ApiErrorException
     */
    private static function _specificAPIError($rbody, $rcode, $rheaders, $resp, $errorData)
    {
        $msg = isset($errorData[0]['detail']) ? $errorData[0]['detail'] : (isset($errorData[0]['title']) ? $errorData[0]['title'] : null) ;
        $param = isset($errorData[0]['param']) ? $errorData[0]['param'] : null;
        $code = isset($errorData[0]['code']) ? $errorData[0]['code'] : null;
        $type = isset($errorData[0]['type']) ? $errorData[0]['type'] : null;

        switch ($rcode) {
            //case 400:
            //    if ('idempotency_error' === $type) {
            //        return Exception\IdempotencyException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
            //    }
                // no break
            case 404:
                return Exception\InvalidRequestException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $param);
            case 401:
                return Exception\AuthenticationException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
            case 403:
                return Exception\PermissionException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
            case 429:
                return Exception\RateLimitException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code, $param);
            default:
                return Exception\UnknownApiErrorException::factory($msg, $rcode, $rbody, $resp, $rheaders, $code);
        }
    }

    /**
     * @static
     *
     * @param null|array $appInfo
     *
     * @return null|string
     */
    private static function _formatAppInfo($appInfo)
    {
        if ($appInfo !== null) {
            $string = $appInfo['name'];
            if ($appInfo['version'] !== null) {
                $string .= '/' . $appInfo['version'];
            }
            if ($appInfo['url'] !== null) {
                $string .= ' (' . $appInfo['url'] . ')';
            }
            return $string;
        } else {
            return null;
        }
    }

    /**
     * @static
     *
     * @param string $apiKey
     * @param null   $clientInfo
     *
     * @return array
     */
    private static function _defaultHeaders($apiKey, $clientInfo = null)
    {
        $uaString = 'Telnyx/v2 PhpBindings/' . Telnyx::VERSION;

        $langVersion = phpversion();
        $uname = php_uname();

        $appInfo = Telnyx::getAppInfo();
        $ua = [
            'bindings_version' => Telnyx::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'telnyx',
            'uname' => $uname,
        ];
        if ($clientInfo) {
            $ua = array_merge($clientInfo, $ua);
        }
        if ($appInfo !== null) {
            $uaString .= ' ' . self::_formatAppInfo($appInfo);
            $ua['application'] = $appInfo;
        }

        $defaultHeaders = [
            'X-Telnyx-Client-User-Agent' => json_encode($ua),
            'User-Agent' => $uaString,
            'Authorization' => 'Bearer ' . $apiKey,
        ];
        return $defaultHeaders;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $params
     * @param array  $headers
     *
     * @throws Exception\AuthenticationException
     * @throws Exception\ApiConnectionException
     *
     * @return array
     */
    private function _requestRaw($method, $url, $params, $headers)
    {
        $myApiKey = $this->_apiKey;
        if (!$myApiKey) {
            $myApiKey = Telnyx::$apiKey;
        }

        if (!$myApiKey) {
            $msg = 'No API key provided.  (HINT: set your API key using '
              . '"Telnyx::setApiKey(<API-KEY>)".  You can generate API keys from '
              . 'the Telnyx web interface.  See https://developers.telnyx.com/docs/v2/development/authentication '
              . 'for details, or email support@telnyx.com if you have any questions.';
            throw new Exception\AuthenticationException($msg);
        }

        // Clients can supply arbitrary additional keys to be included in the
        // X-Telnyx-Client-User-Agent header via the optional getUserAgentInfo()
        // method
        $clientUAInfo = null;
        if (method_exists($this->httpClient(), 'getUserAgentInfo')) {
            $clientUAInfo = $this->httpClient()->getUserAgentInfo();
        }

        $absUrl = $this->_apiBase.$url;

        $params = self::_encodeObjects($params);
        $defaultHeaders = $this->_defaultHeaders($myApiKey, $clientUAInfo);
        if (Telnyx::$apiVersion) {
            $defaultHeaders['Telnyx-Version'] = Telnyx::$apiVersion;
        }

        if (Telnyx::$accountId) {
            $defaultHeaders['Telnyx-Account'] = Telnyx::$accountId;
        }

        if (Telnyx::$enableTelemetry && self::$requestTelemetry != null) {
            $defaultHeaders["X-Telnyx-Client-Telemetry"] = self::_telemetryJson(self::$requestTelemetry);
        }

        $hasFile = false;
        $hasCurlFile = class_exists('\CURLFile', false);
        foreach ($params as $k => $v) {
            if (is_resource($v)) {
                $hasFile = true;
                $params[$k] = self::_processResourceParam($v, $hasCurlFile);
            } elseif ($hasCurlFile && $v instanceof \CURLFile) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $defaultHeaders['Content-Type'] = 'multipart/form-data';
        } else {
            $defaultHeaders['Content-Type'] = 'application/json';
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);
        $rawHeaders = [];

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header . ': ' . $value;
        }

        $requestStartMs = Util\Util::currentTimeMillis();

        list($rbody, $rcode, $rheaders) = $this->httpClient()->request(
            $method,
            $absUrl,
            $rawHeaders,
            $params,
            $hasFile
        );

        if (isset($rheaders['request-id'])) {
            self::$requestTelemetry = new RequestTelemetry(
                $rheaders['request-id'],
                Util\Util::currentTimeMillis() - $requestStartMs
            );
        }

        return [$rbody, $rcode, $rheaders, $myApiKey];
    }

    /**
     * @param resource $resource
     * @param bool     $hasCurlFile
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return \CURLFile|string
     */
    private function _processResourceParam($resource, $hasCurlFile)
    {
        if (get_resource_type($resource) !== 'stream') {
            throw new Exception\InvalidArgumentException(
                'Attempted to upload a resource that is not a stream'
            );
        }

        $metaData = stream_get_meta_data($resource);
        if ($metaData['wrapper_type'] !== 'plainfile') {
            throw new Exception\InvalidArgumentException(
                'Only plainfile resource streams are supported'
            );
        }

        if ($hasCurlFile) {
            // We don't have the filename or mimetype, but the API doesn't care
            return new \CURLFile($metaData['uri']);
        } else {
            return '@'.$metaData['uri'];
        }
    }

    /**
     * @param string $rbody
     * @param int    $rcode
     * @param array  $rheaders
     *
     * @throws Exception\UnexpectedValueException
     * @throws Exception\ApiErrorException
     *
     * @return array
     */
    private function _interpretResponse($rbody, $rcode, $rheaders)
    {
        $resp = json_decode($rbody, true);

        if (isset($resp['data'])) {
            // See if this is NOT a \Telnyx\Collection because Collections are not explicitly specified by the API
            if (isset($resp['data']['record_type']) && !isset($resp['data'][0])) {
                $resp = $resp['data']; // Move [data] to the parent node because it is a single entry, not a collection
            }
        }

        $jsonError = json_last_error();
        if ($resp === null && $jsonError !== JSON_ERROR_NONE) {
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode, json_last_error() was $jsonError)";

            throw new Exception\UnexpectedValueException($msg, $rcode);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleErrorResponse($rbody, $rcode, $rheaders, $resp);
        }
        
        return $resp;
    }

    /**
     * @static
     *
     * @param HttpClient\ClientInterface $client
     */
    public static function setHttpClient($client)
    {
        self::$_httpClient = $client;
    }

    /**
     * @static
     *
     * Resets any stateful telemetry data
     */
    public static function resetTelemetry()
    {
        self::$requestTelemetry = null;
    }

    /**
     * @return HttpClient\ClientInterface
     */
    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance();
        }
        return self::$_httpClient;
    }
}
