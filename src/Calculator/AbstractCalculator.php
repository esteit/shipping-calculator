<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\CalculationResult;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AbstractCalculator
 */
abstract class AbstractCalculator
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @return $this
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        if (is_null($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }

    /**
     * @param PackageInterface $package
     * @return CalculationResult
     */
    final public function calculate(PackageInterface $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $result = new CalculationResult();
        $result->setPackage($package);
        $result->setCalculator($this);

        try {
            $result->setTotalCost($this->calculateTotalCost($package));
        } catch (BasicExceptionInterface $e) {
            $result->setError($e);
        }

        $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($result));

        return $result;
    }

    /**
     * @param PackageInterface $package
     * @return int|float|string
     */
    abstract protected function calculateTotalCost(PackageInterface $package);
}
