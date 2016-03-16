<?php

namespace EsteIt\ShippingCalculator\Exception;

class ViolationException extends LogicException
{
    protected $message = 'Violation occurred.';
}
