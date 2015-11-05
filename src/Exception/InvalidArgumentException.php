<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class InvalidArgumentException
 */
class InvalidArgumentException extends \InvalidArgumentException implements BasicExceptionInterface
{
    protected $message = 'Invalid argument exception.';
}
