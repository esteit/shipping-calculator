<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;
use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCalculator implements CalculatorInterface
{
    const RESULT_INTERFACE = 'EsteIt\ShippingCalculator\Model\CalculationResultInterface';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var CalculatorHandlerInterface
     */
    protected $handler;

    /**
     * @var string
     */
    protected $resultClass;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'dispatcher' => new EventDispatcher(),
                'result_class' => 'EsteIt\ShippingCalculator\Model\CalculationResult',
            ])
            ->setRequired([
                'dispatcher',
                'handler',
            ])
            ->setAllowedTypes([
                'dispatcher' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                'result_class' => 'string',
                'handler' => 'EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface',
            ]);

        $options = $resolver->resolve($options);

        $this->setDispatcher($options['dispatcher']);
        $this->setResultClass($options['result_class']);
        $this->setHandler($options['handler']);
    }

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
        return $this->resultClass;
    }

    /**
     * @param CalculatorHandlerInterface $handler
     * @return $this
     */
    public function setHandler(CalculatorHandlerInterface $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return CalculatorHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param PackageInterface $package
     * @return CalculationResultInterface
     */
    public function calculate(PackageInterface $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $result = $this->createResult();
        $result->setPackage($package);

        try {
            $this->getDispatcher()->dispatch(Events::BEFORE_HANDLE, new BeforeHandleEvent($this, $this->getHandler(), $package));
            $this->getHandler()->visit($result, $package);
            $this->getDispatcher()->dispatch(Events::AFTER_HANDLE, new AfterHandleEvent($this, $this->getHandler(), $result));
        } catch (BasicExceptionInterface $e) {
            $result->setError($e);
        }

        $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($this, $result));

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
}
