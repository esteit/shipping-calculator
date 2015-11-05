<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class UnsuitableDeliveryMethodException
 */
class UnsuitableDeliveryMethodException extends LogicException
{
    protected $message = 'Unsuitable delivery method.';
}
