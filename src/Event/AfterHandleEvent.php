<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use Symfony\Component\EventDispatcher\Event;

class AfterHandleEvent extends Event
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
     * @var CalculationResultInterface
     */
    protected $result;

    /**
     * @param CalculatorInterface $calculator
     * @param CalculatorHandlerInterface $handler
     * @param CalculationResultInterface $result
     */
    public function __construct(CalculatorInterface $calculator, CalculatorHandlerInterface $handler, CalculationResultInterface $result)
    {
        $this->calculator = $calculator;
        $this->handler = $handler;
        $this->result = $result;
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
     * @return CalculationResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }
}
