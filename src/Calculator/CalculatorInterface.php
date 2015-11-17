<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;

interface CalculatorInterface
{
    /**
     * @param PackageInterface $package
     * @return CalculationResultInterface
     */
    public function calculate(PackageInterface $package);
}
