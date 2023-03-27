<?php

namespace Telnyx\Exception;

/**
 * RateLimitException is thrown in cases where an account is putting too much
 * load on Telnyx's API servers (usually by performing too many requests).
 * Please back off on request rate.
 *
 * @package Telnyx\Exception
 */
class RateLimitException extends InvalidRequestException
{
}
