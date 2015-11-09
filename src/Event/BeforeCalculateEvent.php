<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\AbstractCalculator;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BeforeCalculateEvent
 */
class BeforeCalculateEvent extends Event
{
    /**
     * @var AbstractCalculator
     */
    protected $calculator;

    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * BeforeCalculateEvent constructor.
     *
     * @param AbstractCalculator $calculator
     * @param PackageInterface        $package
     */
    public function __construct(AbstractCalculator $calculator, PackageInterface $package)
    {
        $this->calculator = $calculator;
        $this->package = $package;
    }

    /**
     * @return AbstractCalculator
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
