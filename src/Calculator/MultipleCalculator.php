<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Handler\HandlerInterface;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MultipleCalculator
 */
class MultipleCalculator implements CalculatorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];
    /**
     * @var string
     */
    protected $resultClass;

    /**
     * @param array $options
     */
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
                'handlers',
            ])
            ->setAllowedTypes([
                'dispatcher' => EventDispatcherInterface::class,
                'result_class' => 'string',
                'handlers' => 'array',
            ]);
        $options = $resolver->resolve($options);
        $this->setDispatcher($options['dispatcher']);
        $this->setResultClass($options['result_class']);
        $this->setHandlers($options['handlers']);
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

    /**
     * @return string
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * @param HandlerInterface[] $handlers
     * @return $this
     */
    public function setHandlers(array $handlers)
    {
        $this->handlers = [];
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    /**
     * @param HandlerInterface $handler
     * @return $this
     */
    public function addHandler(HandlerInterface $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param Package $package
     * @return Result
     */
    public function calculate(Package $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $results = [];

        foreach ($this->getHandlers() as $handler) {
            $result = $this->createResult();
            $result->setPackage($package);
            $this->getDispatcher()->dispatch(Events::BEFORE_HANDLE, new BeforeHandleEvent($this, $handler, $package));
            $handler->calculate($result, $package);
            $this->getDispatcher()->dispatch(Events::AFTER_HANDLE, new AfterHandleEvent($this, $handler, $result));
            $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterCalculateEvent($this, $result));
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return Result
     */
    protected function createResult()
    {
        $resultClass = $this->getResultClass();

        return new $resultClass();
    }
}
