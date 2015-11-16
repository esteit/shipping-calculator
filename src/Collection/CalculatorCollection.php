<?php

namespace EsteIt\ShippingCalculator\Collection;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Moriony\Trivial\Collection\ArrayCollection;

class CalculatorCollection extends ArrayCollection
{
    /**
     * @param PackageInterface $package
     * @return CalculationResultInterface[]
     */
    public function calculate(PackageInterface $package)
    {
        $results = [];

        /** @var CalculatorInterface $calculator */
        foreach ($this->toArray() as $calculator) {
            $results[] = $calculator->calculate($package);
        }

        return $results;
    }
}
