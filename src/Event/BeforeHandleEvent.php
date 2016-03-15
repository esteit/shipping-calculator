<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Handler\HandlerInterface;
use EsteIt\ShippingCalculator\Package;
use Symfony\Component\EventDispatcher\Event;

class BeforeHandleEvent extends Event
{
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @var HandlerInterface
     */
    protected $handler;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @param CalculatorInterface $calculator
     * @param HandlerInterface $handler
     * @param Package $package
     */
    public function __construct(CalculatorInterface $calculator, HandlerInterface $handler, Package $package)
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
     * @return HandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }
}
