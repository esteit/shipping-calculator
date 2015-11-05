<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class RuntimeException
 */
class RuntimeException extends \RuntimeException implements BasicExceptionInterface
{
    protected $message = 'Something went wrong.';
}
