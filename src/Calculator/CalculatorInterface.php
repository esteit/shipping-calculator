<?php

namespace EsteIt\PackageDeliveryCalculator\Calculator;

use EsteIt\PackageDeliveryCalculator\CalculationResult;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;

/**
 * Interface CalculatorInterface
 */
interface CalculatorInterface
{
    /**
     * @param PackageInterface $package
     * @return CalculationResult
     */
    public function calculate(PackageInterface $package);
}
