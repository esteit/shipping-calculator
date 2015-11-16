<?php

namespace EsteIt\ShippingCalculator\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements BasicExceptionInterface
{
    protected $message = 'Invalid argument exception.';
}
