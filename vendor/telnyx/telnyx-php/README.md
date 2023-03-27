# Telnyx PHP SDK

[![Build Status](https://github.com/team-telnyx/telnyx-php/workflows/Composer/badge.svg)](https://github.com/team-telnyx/telnyx-php/actions)
[![Latest Stable Version](https://poser.pugx.org/telnyx/telnyx-php/v/stable.svg)](https://packagist.org/packages/telnyx/telnyx-php)
[![Total Downloads](https://poser.pugx.org/telnyx/telnyx-php/downloads.svg)](https://packagist.org/packages/telnyx/telnyx-php)
[![License](https://poser.pugx.org/telnyx/telnyx-php/license.svg)](https://packagist.org/packages/telnyx/telnyx-php)
[![Code Coverage](https://coveralls.io/repos/github/team-telnyx/telnyx-php/badge.svg?branch=master&)](https://coveralls.io/github/team-telnyx/telnyx-php?branch=master&)
[![Join Slack](https://img.shields.io/badge/join-slack-infomational)](https://joinslack.telnyx.com/)

The Telnyx PHP library provides convenient access to the Telnyx API from
applications written in PHP. It includes a pre-defined set of
classes for API resources that initialize themselves dynamically from API
responses.

The library also provides other features. For example:

* Easy configuration path for fast setup and use.
* Helpers for pagination.
* Built-in mechanisms for the serialization of parameters according to the
  expectations of Telnyx's API.

You can sign up for a Telnyx account at [telnyx.com](https://telnyx.com).


## Requirements

PHP 5.6.0 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
$ composer require telnyx/telnyx-php
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/team-telnyx/telnyx-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/telnyx-php/init.php');
```


## Dependencies

Some PHP extensions are required:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)
- [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php) (Multibyte String)

Composer will handle these dependencies. If you install manually, you'll want to make sure that these extensions are available.


## Getting Started

Basic example:

```php
\Telnyx\Telnyx::setApiKey('YOUR_API_KEY_HERE');

// Get a list of messaging profiles
$result = \Telnyx\MessagingProfile::all();

// Output list of messaging profiles
print_r($result);
```


## Documentation

Please see https://developers.telnyx.com/docs/api/v2/overview for up-to-date documentation.


## Custom Request Timeouts

To modify request timeouts (connect or total, in seconds) you'll need to tell the API client to use a CurlClient other than its default. You'll set the timeouts in that CurlClient.

```php
// set up your tweaked Curl client
$curl = new \Telnyx\HttpClient\CurlClient();
$curl->setTimeout(10); // default is Telnyx\HttpClient\CurlClient::DEFAULT_TIMEOUT
$curl->setConnectTimeout(5); // default is Telnyx\HttpClient\CurlClient::DEFAULT_CONNECT_TIMEOUT

echo $curl->getTimeout(); // 10
echo $curl->getConnectTimeout(); // 5

// tell Telnyx to use the tweaked client
\Telnyx\ApiRequestor::setHttpClient($curl);

// use the Telnyx API client as you normally would
```


## Custom cURL Options (proxies)

Need to set a proxy for your requests? Pass in the requisite `CURLOPT_*` array to the CurlClient constructor, using the same syntax as `curl_stopt_array()`. This will set the default cURL options for each HTTP request made by the SDK, though many more common options (e.g. timeouts; see above on how to set those) will be overridden by the client even if set here.

```php
// set up your tweaked Curl client
$curl = new \Telnyx\HttpClient\CurlClient([CURLOPT_PROXY => 'proxy.local:80']);
// tell Telnyx to use the tweaked client
\Telnyx\ApiRequestor::setHttpClient($curl);
```

Alternately, a callable can be passed to the CurlClient constructor that returns the above array based on request inputs. See `testDefaultOptions()` in `tests/CurlClientTest.php` for an example of this behavior. Note that the callable is called at the beginning of every API request, before the request is sent.


## Configuring a Logger

The library does minimal logging, but it can be configured
with a [`PSR-3` compatible logger][psr3] so that messages
end up there instead of `error_log`:

```php
\Telnyx\Telnyx::setLogger($logger);
```


## Accessing Response Data

You can access the data from the last API response on any object via `getLastResponse()`.

```php
$order = \Telnyx\NumberOrder::create(['phone_number' => '+18665552368']);
print_r($order->getLastResponse()->headers);
```


## SSL / TLS Compatibility issues

Telnyx's API now requires that all connections use TLS 1.2. Some systems (most notably some older CentOS and RHEL versions) are capable of using TLS 1.2 but will use TLS 1.0 or 1.1 by default.

The recommended course of action is to upgrade your cURL and OpenSSL packages so that TLS 1.2 is used by default, but if that is not possible, you might be able to solve the issue by setting the `CURLOPT_SSLVERSION` option to either `CURL_SSLVERSION_TLSv1` or `CURL_SSLVERSION_TLSv1_2`:

```php
$curl = new \Telnyx\HttpClient\CurlClient([CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1]);
\Telnyx\ApiRequestor::setHttpClient($curl);
```


## Per-request Configuration

For apps that need to use multiple keys during the lifetime of a process it's also possible to set a
per-request key and/or account:

```php
\Telnyx\NumberOrder::all([], [
    'api_key' => 'KEY0123...'
]);

\Telnyx\NumberOrder::retrieve("12ade33a-21c0-473b-b055-b3c836e1c292", [
    'api_key' => 'KEY0123...'
]);
```


## Automatic Retries

The library can be configured to automatically retry requests that fail due to
an intermittent network problem:

```php
\Telnyx\Telnyx::setMaxNetworkRetries(2);
```


## Development

Unit tests rely on a mock server so all unit tests are ran through
docker.  To run all unit tests execute:

```
docker-compose run --rm php composer test
```

Running unit tests with code coverage requires you build the docker
container with XDEBUG=1

```
docker-compose build --build-arg XDEBUG=1
```

then run the unit tests as

```
docker-compose run --rm php composer test-coverage
```


## Plugin Developers

Are you writing a plugin that integrates Telnyx and embeds our library? Then please use the `setAppInfo` function to identify your plugin. For example:

```php
\Telnyx\Telnyx::setAppInfo("MyCustomPlugin", "1.2.3", "https://customplugin.yoursite.com");
```

The method should be called once before any request is sent to the API. The second and third parameters are optional.


## SSL / TLS Configuration Option

See the "SSL / TLS compatibility issues" paragraph above for full context. If you want to
ensure that your plugin can be used on all systems, you should add a configuration option
to let your users choose between different values for
`CURLOPT_SSLVERSION`: none (default), `CURL_SSLVERSION_TLSv1` and `CURL_SSLVERSION_TLSv1_2`.


## Acknowledgments

The contributors and maintainers of Telnyx PHP would like to extend their deep gratitude
to the authors of [Stripe PHP][stripe-php], upon which this project is based. Thank you
for developing such elegant, usable, and extensible code and for sharing it with the community.

[stripe-php]: https://github.com/stripe/stripe-php
[composer]: https://getcomposer.org/
[curl]: http://curl.haxx.se/docs/caextract.html
[psr3]: http://www.php-fig.org/psr/psr-3/

