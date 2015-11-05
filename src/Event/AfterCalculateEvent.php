<?php

namespace Rage\PackageDeliveryCalculator\Event;

use Rage\PackageDeliveryCalculator\CalculationResult;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AfterCalculateEvent
 */
class AfterCalculateEvent extends Event
{
    /**
     * @var CalculationResult
     */
    protected $result;

    /**
     * AfterCalculateEvent constructor.
     *
     * @param CalculationResult $result
     */
    public function __construct(CalculationResult $result)
    {
        $this->result = $result;
    }

    /**
     * @return CalculationResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
