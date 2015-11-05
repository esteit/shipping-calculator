<?php

namespace EsteIt\PackageDeliveryCalculator;

use EsteIt\PackageDeliveryCalculator\Calculator\CalculatorInterface;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;

/**
 * Class CalculationResult
 */
class CalculationResult
{
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * @var string|int|float
     */
    protected $totalCost;

    /**
     * @param string|int|float $totalCost
     * @return $this
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * @return string|int|float
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param CalculatorInterface $calculator
     * @return $this
     */
    public function setCalculator(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;

        return $this;
    }

    /**
     * @return CalculatorInterface
     */
    public function getCalculator()
    {
        return $this->calculator;
    }

    /**
     * @param PackageInterface $package
     * @return $this
     */
    public function setPackage(PackageInterface $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
