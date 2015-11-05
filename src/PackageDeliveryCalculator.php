<?php

namespace EsteIt\PackageDeliveryCalculator;

use EsteIt\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface;
use EsteIt\PackageDeliveryCalculator\Event\AfterCalculateEvent;
use EsteIt\PackageDeliveryCalculator\Event\BeforeCalculateEvent;
use EsteIt\PackageDeliveryCalculator\Event\Events;
use EsteIt\PackageDeliveryCalculator\Exception\InvalidArgumentException;
use EsteIt\PackageDeliveryCalculator\Package\PackageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class PackageDeliveryCalculator
 */
class PackageDeliveryCalculator
{
    /**
     * @var DeliveryMethodInterface[]
     */
    protected $deliveryMethods;

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
        $this->deliveryMethods = [];

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
     * @param DeliveryMethodInterface $deliveryMethod
     * @return $this
     */
    public function addDeliveryMethod($name, DeliveryMethodInterface $deliveryMethod)
    {
        $this->deliveryMethods[$name] = $deliveryMethod;

        return $this;
    }

    /**
     * @param string $name
     * @return DeliveryMethodInterface
     */
    public function getDeliveryMethod($name)
    {
        if (!array_key_exists($name, $this->deliveryMethods)) {
            throw new InvalidArgumentException('Delivery method was not found.');
        }

        return $this->deliveryMethods[$name];
    }

    /**
     * @param PackageInterface $package
     * @return array
     */
    public function calculate(PackageInterface $package)
    {
        $results = [];
        foreach ($this->deliveryMethods as $deliveryMethod) {
            $this->dispatcher->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($deliveryMethod, $package));
            $result = $deliveryMethod->calculate($package);
            $this->dispatcher->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($result));
            $results[] = $result;
        }

        return $results;
    }
}
