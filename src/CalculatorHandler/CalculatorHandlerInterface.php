<?php

namespace EsteIt\ShippingCalculator\CalculatorHandler;

use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;

interface CalculatorHandlerInterface
{
    /**
     * @param string|int $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param CalculationResultInterface $result
     * @param PackageInterface $package
     * @return void
     */
    public function visit(CalculationResultInterface $result, PackageInterface $package);
}
