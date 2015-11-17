<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use Symfony\Component\EventDispatcher\Event;

class AfterCalculateEvent extends Event
{
    /**
     * @var CalculationResultInterface
     */
    protected $result;
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @param CalculatorInterface $calculator
     * @param CalculationResultInterface $result
     */
    public function __construct(CalculatorInterface $calculator, CalculationResultInterface $result)
    {
        $this->result = $result;
        $this->calculator = $calculator;
    }

    /**
     * @return CalculationResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return CalculatorInterface
     */
    public function getCalculator()
    {
        return $this->calculator;
    }
}
