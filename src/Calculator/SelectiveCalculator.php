<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;
use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Exception\LogicException;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\Exception\BasicExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Closure;

class SelectiveCalculator implements CalculatorInterface
{
    const RESULT_INTERFACE = 'EsteIt\ShippingCalculator\Model\CalculationResultInterface';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var CalculatorHandlerInterface[]
     */
    protected $handlers;

    /**
     * @var Closure
     */
    protected $selector;

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
                'handlers',
                'selector'
            ])
            ->setAllowedTypes([
                'dispatcher' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                'result_class' => 'string',
                'handlers' => 'array',
                'selector' => 'Closure',
            ]);

        $options = $resolver->resolve($options);

        $this->setDispatcher($options['dispatcher']);
        $this->setResultClass($options['result_class']);
        $this->setSelector($options['selector']);
        $this->addHandlers($options['handlers']);
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

    /**
     * @return string
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * @param CalculatorHandlerInterface $handler
     * @return $this
     */
    public function addHandler(CalculatorHandlerInterface $handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @param CalculatorHandlerInterface[] $handlers
     * @return $this
     */
    public function addHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->addHandler($handler);
        }

        return $this;
    }

    /**
     * @return CalculatorHandlerInterface[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param Closure $selector
     * @return $this
     */
    public function setSelector(Closure $selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * @return Closure
     */
    public function getSelector()
    {
        return $this->selector;
    }

    /**
     * @param PackageInterface $package
     * @return CalculationResultInterface
     */
    public function calculate(PackageInterface $package)
    {
        $this->getDispatcher()->dispatch(Events::BEFORE_CALCULATE, new BeforeCalculateEvent($this, $package));

        $selector = $this->getSelector();

        $handler = $selector($this->getHandlers(), $package);
        if (!$handler instanceof CalculatorHandlerInterface) {
            throw new LogicException('Calculator was not found.');
        }

        $result = $this->createResult();
        $result->setPackage($package);

        try {
            $this->getDispatcher()->dispatch(Events::BEFORE_HANDLE, new BeforeHandleEvent($this, $handler, $package));
            $handler->visit($result, $package);
            $this->getDispatcher()->dispatch(Events::AFTER_CALCULATE, new AfterHandleEvent($this, $handler, $result));
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
