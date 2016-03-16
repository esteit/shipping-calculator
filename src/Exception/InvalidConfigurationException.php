<?php

namespace EsteIt\ShippingCalculator\Exception;

class InvalidConfigurationException extends LogicException
{
    protected $message = 'Invalid configuration.';
}
