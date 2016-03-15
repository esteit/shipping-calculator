<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Handler\HandlerInterface;
use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseCalculator implements CalculatorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var HandlerInterface
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
                'result_class' => Result::class,
            ])
            ->setRequired([
                'dispatcher',
                'handler',
            ])
            ->setAllowedTypes([
                'dispatcher' => EventDispatcherInterface::class,
                'result_class' => 'string',
                'handler' => HandlerInterface::class,
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
        $parents = class_parents($class);
        $parents[] = $class;

        if (!in_array(Result::class, $parents)) {
            throw new InvalidArgumentException(sprintf('Result class must extends class "%s"', Result::class));
        }
        $this->resultClass = $class;

        return $this;
    }

    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * @param HandlerInterface $handler
     * @return $this
     */
    public function setHandler(HandlerInterface $handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return HandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param Package $package
     * @return Result
     */
    public function calculate(Package $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $result = $this->createResult();
        $result->setPackage($package);

        $this->getDispatcher()->dispatch(Events::BEFORE_HANDLE, new BeforeHandleEvent($this, $this->getHandler(), $package));
        $this->getHandler()->calculate($result, $package);
        $this->getDispatcher()->dispatch(Events::AFTER_HANDLE, new AfterHandleEvent($this, $this->getHandler(), $result));

        $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($this, $result));

        return $result;
    }

    /**
     * @return Result
     */
    protected function createResult()
    {
        $resultClass = $this->getResultClass();
        return new $resultClass;
    }
}
