<?php

namespace EsteIt\ShippingCalculator\Exception;

class LogicException extends \LogicException implements BasicExceptionInterface
{
    protected $message = 'Something went wrong.';
}
