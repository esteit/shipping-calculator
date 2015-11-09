<?php

namespace EsteIt\ShippingCalculator;

use EsteIt\ShippingCalculator\Calculator\AbstractCalculator;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Model\PackageInterface;

/**
 * Class ShippingCalculationService
 */
class ShippingCalculationService
{
    /**
     * @var AbstractCalculator[]
     */
    protected $calculators = [];

    /**
     * @param string                  $name
     * @param AbstractCalculator $calculator
     * @return $this
     */
    public function addCalculator($name, AbstractCalculator $calculator)
    {
        $this->calculators[$name] = $calculator;

        return $this;
    }

    /**
     * @param string $name
     * @return AbstractCalculator
     */
    public function getCalculator($name)
    {
        if (!array_key_exists($name, $this->calculators)) {
            throw new InvalidArgumentException('Calculator was not found.');
        }

        return $this->calculators[$name];
    }

    /**
     * @param PackageInterface $package
     * @return array
     */
    public function calculate(PackageInterface $package)
    {
        $results = [];
        foreach ($this->calculators as $calculator) {
            $results[] = $calculator->calculate($package);
        }

        return $results;
    }
}
