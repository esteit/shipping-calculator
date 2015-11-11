<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class InvalidWeightException
 */
class InvalidDimensionsException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_DIMENSIONS;
    protected $message = 'Invalid dimensions.';
}
