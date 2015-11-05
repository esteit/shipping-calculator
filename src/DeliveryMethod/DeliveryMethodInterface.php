<?php

namespace Rage\PackageDeliveryCalculator\DeliveryMethod;

use Rage\PackageDeliveryCalculator\CalculationResult;
use Rage\PackageDeliveryCalculator\Package\PackageInterface;

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
