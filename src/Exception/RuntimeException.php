<?php

namespace EsteIt\PackageDeliveryCalculator\Exception;

/**
 * Class RuntimeException
 */
class RuntimeException extends \RuntimeException implements BasicExceptionInterface
{
    protected $message = 'Something went wrong.';
}
