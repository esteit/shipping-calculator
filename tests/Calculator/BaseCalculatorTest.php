<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\BaseCalculator;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Exception\LogicException;
use EsteIt\ShippingCalculator\Handler\HandlerInterface;
use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group unit
 */
class BaseCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchCall()
    {
        $dispatcher = \Mockery::mock(EventDispatcherInterface::class)
            ->shouldReceive('dispatch')
            ->times(4)
            ->getMock();

        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
            'dispatcher' => $dispatcher,
        ]);
        $package = \Mockery::mock(Package::class);
        $calculator->setDispatcher($dispatcher);
        $calculator->calculate($package);
    }

    public function testDispatchBeforeCalculateEvent()
    {
        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
        ]);

        $package = new Package();

        $calculator->getDispatcher()->addListener('before_calculate', function ($e) use ($calculator, $package) {
            /** @var BeforeCalculateEvent $e */
            $this->assertInstanceOf(BeforeCalculateEvent::class, $e);
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertSame($package, $e->getPackage());
        });

        $calculator->calculate($package);
    }

    public function testDispatchAfterCalculateEvent()
    {
        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
        ]);

        $package = new Package();

        $calculator->getDispatcher()->addListener('after_calculate', function ($e) use ($calculator, $package) {
            /** @var AfterCalculateEvent $e */
            $this->assertInstanceOf(AfterCalculateEvent::class, $e);
            $this->assertInstanceOf(Result::class, $e->getResult());
            $this->assertSame($package, $e->getResult()->getPackage());
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertEmpty($e->getResult()->getViolations());
        });

        $calculator->calculate($package);
    }

    public function testDispatchBeforeHandleEvent()
    {
        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
        ]);

        $package = new Package();

        $calculator->getDispatcher()->addListener('before_handle', function ($e) use ($calculator, $package) {
            /** @var BeforeHandleEvent $e */
            $this->assertInstanceOf(BeforeHandleEvent::class, $e);
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertSame($calculator->getHandler(), $e->getHandler());
            $this->assertSame($package, $e->getPackage());
        });

        $calculator->calculate($package);
    }

    public function testDispatchAfterHandleEvent()
    {
        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
        ]);

        $package = new Package();

        $calculator->getDispatcher()->addListener('after_handle', function ($e) use ($calculator, $package) {
            /** @var AfterHandleEvent $e */
            $this->assertInstanceOf(AfterHandleEvent::class, $e);
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertSame($calculator->getHandler(), $e->getHandler());
            $this->assertInstanceOf(Result::class, $e->getResult());
        });

        $calculator->calculate($package);
    }

    public function testUnexpectedException()
    {
        $this->setExpectedException('Exception');

        $handler = \Mockery::mock(HandlerInterface::class.'[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('calculate')
            ->andThrow('Exception')
            ->getMock();

        $calculator = new BaseCalculator([
            'handler' => $handler,
        ]);

        $calculator->calculate(new Package());
    }

    /**
     * @return \Mockery\MockInterface|HandlerInterface
     */
    public function getCalculatorHandlerMock()
    {
        return \Mockery::mock(HandlerInterface::class.'[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('calculate')
            ->getMock();
    }
}
