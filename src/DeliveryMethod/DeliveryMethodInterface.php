<?php

namespace EsteIt\PackageDeliveryCalculator\DeliveryMethod;

use EsteIt\PackageDeliveryCalculator\CalculationResult;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;

/**
 * Interface DeliveryMethodInterface
 */
interface DeliveryMethodInterface
{
    /**
     * @param PackageInterface $package
     * @return CalculationResult
     */
    public function calculate(PackageInterface $package);
}
