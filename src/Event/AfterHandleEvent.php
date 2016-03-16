<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Calculator\CalculatorInterface;
use EsteIt\ShippingCalculator\Handler\HandlerInterface;
use EsteIt\ShippingCalculator\Result;
use Symfony\Component\EventDispatcher\Event;

class AfterHandleEvent extends Event
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
     * @var Result
     */
    protected $result;

    /**
     * @param CalculatorInterface $calculator
     * @param HandlerInterface $handler
     * @param Result $result
     */
    public function __construct(CalculatorInterface $calculator, HandlerInterface $handler, Result $result)
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
     * @return HandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }
}
