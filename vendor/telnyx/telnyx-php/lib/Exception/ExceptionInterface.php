<?php

namespace Telnyx\Exception;

// TODO: remove this check once we drop support for PHP 5
if (interface_exists(\Throwable::class)) {
    /**
     * The base interface for all Telnyx exceptions.
     *
     * @package Telnyx\Exception
     */
    interface ExceptionInterface extends \Throwable
    {
    }
} else {
    /**
     * The base interface for all Telnyx exceptions.
     *
     * @package Telnyx\Exception
     */
    // phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
    interface ExceptionInterface
    {
    }
    // phpcs:enable
}
