<?php

namespace EsteIt\ShippingCalculator\Event;

use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterCalculateEvent
 */
class AfterCalculateEvent extends Event
{
    /**
     * @var CalculationResultInterface
     */
    protected $result;

    /**
     * AfterCalculateEvent constructor.
     *
     * @param CalculationResultInterface $result
     */
    public function __construct(CalculationResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * @return CalculationResultInterface
     */
    public function getResult()
    {
        return $this->result;
    }
}
