<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Symfony\Component\EventDispatcher\Event;

class BeforeHandleEvent extends Event
{
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var CalculatorHandlerInterface
     */
    protected $handler;

    /**
     * @var PackageInterface
     */
    protected $package;

    /**
     * @param CalculatorInterface $calculator
     * @param CalculatorHandlerInterface $handler
     * @param PackageInterface $package
     */
    public function __construct(CalculatorInterface $calculator, CalculatorHandlerInterface $handler, PackageInterface $package)
    {
        $this->calculator = $calculator;
        $this->handler = $handler;
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
     * @return CalculatorHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
