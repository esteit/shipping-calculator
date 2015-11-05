<?php

namespace EsteIt\PackageDeliveryCalculator;

use EsteIt\PackageDeliveryCalculator\Calculator\CalculatorInterface;
use EsteIt\PackageDeliveryCalculator\Event\AfterCalculateEvent;
use EsteIt\PackageDeliveryCalculator\Event\BeforeCalculateEvent;
use EsteIt\PackageDeliveryCalculator\Event\Events;
use EsteIt\PackageDeliveryCalculator\Exception\InvalidArgumentException;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class PackageDeliveryCalculationService
 */
class PackageDeliveryCalculationService
{
    /**
     * @var CalculatorInterface[]
     */
    protected $calculators;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * PackageDeliveryCalculator constructor.
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->calculators = [];

        if (is_null($dispatcher)) {
            $dispatcher = new EventDispatcher();
        }
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param string                  $name
     * @param CalculatorInterface $calculator
     * @return $this
     */
    public function addCalculator($name, CalculatorInterface $calculator)
    {
        $this->calculators[$name] = $calculator;

        return $this;
    }

    /**
     * @param string $name
     * @return CalculatorInterface
     */
    public function getCalculator($name)
    {
        if (!array_key_exists($name, $this->calculators)) {
            throw new InvalidArgumentException('Delivery method was not found.');
        }

        return $this->calculators[$name];
    }

    /**
     * @param PackageInterface $package
     * @return array
     */
    public function calculate(PackageInterface $package)
    {
        $results = [];
        foreach ($this->calculators as $calculator) {
            $this->dispatcher->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($calculator, $package));
            $result = $calculator->calculate($package);
            $this->dispatcher->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($result));
            $results[] = $result;
        }

        return $results;
    }
}
