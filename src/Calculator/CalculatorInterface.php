<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;

interface CalculatorInterface
{
    /**
     * @param Package $package
     * @return Result
     */
    public function calculate(Package $package);
}
