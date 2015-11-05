<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\CalculationResult;
use EsteIt\ShippingCalculator\Package\PackageInterface;

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
