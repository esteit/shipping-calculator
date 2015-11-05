<?php

namespace EsteIt\PackageDeliveryCalculator\Event;

use EsteIt\PackageDeliveryCalculator\Calculator\CalculatorInterface;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeCalculateEvent
 */
class BeforeCalculateEvent extends Event
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
     * BeforeCalculateEvent constructor.
     *
     * @param CalculatorInterface $calculator
     * @param PackageInterface        $package
     */
    public function __construct(CalculatorInterface $calculator, PackageInterface $package)
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
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
