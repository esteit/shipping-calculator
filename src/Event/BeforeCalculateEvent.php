<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Package;
use Symfony\Component\EventDispatcher\Event;

class BeforeCalculateEvent extends Event
{
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @param CalculatorInterface $calculator
     * @param Package $package
     */
    public function __construct(CalculatorInterface $calculator, Package $package)
    {
        $this->calculator = $calculator;
        $this->package = $package;
    }

    /**
     * @return CalculatorInterface
     */
    public function getCalculator()
    {
        return $this->calculator;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }
}
