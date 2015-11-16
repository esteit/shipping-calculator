<?php

namespace EsteIt\ShippingCalculator\Exception;

class InvalidDimensionsException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_DIMENSIONS;
    protected $message = 'Invalid dimensions.';
}
