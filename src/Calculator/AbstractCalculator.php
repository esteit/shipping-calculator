<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractCalculator implements CalculatorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $resultClass;

    const RESULT_INTERFACE = 'EsteIt\ShippingCalculator\Model\CalculationResultInterface';
    const DEFAULT_RESULT_CLASS = 'EsteIt\ShippingCalculator\Model\CalculationResult';

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
     * @param string $class
     * @return $this
     */
    public function setResultClass($class)
    {
        $interfaces = class_implements($class);
        if (!$interfaces || !in_array(self::RESULT_INTERFACE, $interfaces)) {
            throw new InvalidArgumentException(sprintf('Result class must implement interface "%s"', self::RESULT_INTERFACE));
        }
        $this->resultClass = $class;

        return $this;
    }

    public function getResultClass()
    {
        if (!$this->resultClass) {
            $this->setResultClass(self::DEFAULT_RESULT_CLASS);
        }

        return $this->resultClass;
    }

    /**
     * @param PackageInterface $package
     * @return CalculationResultInterface
     */
    final public function calculate(PackageInterface $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $result = $this->createResult();
        $result->setPackage($package);
        $result->setCalculator($this);

        try {
            $this->visit($result, $package);
        } catch (BasicExceptionInterface $e) {
            $result->setError($e);
        }

        $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($result));

        return $result;
    }

    /**
     * @return CalculationResultInterface
     */
    protected function createResult()
    {
        $resultClass = $this->getResultClass();
        return new $resultClass;
    }

    /**
     * @param CalculationResultInterface $result
     * @param PackageInterface           $package
     * @return int|float|string
     */
    abstract public function visit(CalculationResultInterface $result, PackageInterface $package);

    /**
     * @return mixed
     */
    public function getExtraData()
    {
        return null;
    }

    /**
     * @param mixed $name
     * @return mixed null
     */
    public function getOption($name)
    {
        return $this->options && array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }
}
