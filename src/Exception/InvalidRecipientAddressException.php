<?php

namespace EsteIt\ShippingCalculator\Exception;

class InvalidRecipientAddressException extends LogicException
{
    protected $code = BasicExceptionInterface::CODE_INVALID_RECIPIENT_ADDRESS;
    protected $message = 'Invalid recipient address.';
}
