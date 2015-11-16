<?php

namespace EsteIt\ShippingCalculator\Exception;

class InvalidConfigurationException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_CONFIGURATION;
    protected $message = 'Invalid configuration.';
}
