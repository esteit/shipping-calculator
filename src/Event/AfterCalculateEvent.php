<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Result;
use Symfony\Component\EventDispatcher\Event;

class AfterCalculateEvent extends Event
{
    /**
     * @var Result
     */
    protected $result;
    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * @param CalculatorInterface $calculator
     * @param Result $result
     */
    public function __construct(CalculatorInterface $calculator, Result $result)
    {
        $this->result = $result;
        $this->calculator = $calculator;
    }

    /**
     * @return Result
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
