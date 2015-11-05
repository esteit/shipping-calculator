<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class LogicException
 */
class LogicException extends \LogicException implements BasicExceptionInterface
{
    protected $message = 'Something went wrong.';
}
