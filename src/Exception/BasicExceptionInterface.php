<?php

namespace EsteIt\ShippingCalculator\Exception;

/**
 * Class BasicExceptionInterface
 */
interface BasicExceptionInterface
{
    // Basic error codes
    const CODE_CAN_NOT_CALCULATE = 101;
    const CODE_INVALID_CONFIGURATION = 102;

    // Package error codes
    const CODE_INVALID_PACKAGE = 200;
    const CODE_INVALID_WEIGHT = 201;

    // Sender address error codes
    const CODE_INVALID_SENDER_ADDRESS = 300;

    // Recipient address error codes
    const CODE_INVALID_RECIPIENT_ADDRESS = 400;
}
