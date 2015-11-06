<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class InvalidWeightException
 */
class InvalidWeightException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_WEIGHT;
    protected $message = 'Invalid weight.';
}
