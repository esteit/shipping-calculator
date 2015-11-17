<?php

namespace EsteIt\ShippingCalculator\Exception;

class LogicException extends \LogicException implements BasicExceptionInterface
{
    protected $code = self::CODE_CAN_NOT_CALCULATE;
    protected $message = 'Something went wrong.';
}
