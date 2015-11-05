<?php

namespace Rage\PackageDeliveryCalculator\Exception;

/**
 * Class UnsuitableDeliveryMethodException
 */
class UnsuitableDeliveryMethodException extends LogicException
{
    protected $message = 'Unsuitable delivery method.';
}
