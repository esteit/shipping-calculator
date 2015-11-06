<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class InvalidSenderAddressException
 */
class InvalidSenderAddressException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_SENDER_ADDRESS;
    protected $message = 'Invalid sender address.';
}
