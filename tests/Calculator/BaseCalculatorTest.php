<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\AbstractCalculator;
use EsteIt\ShippingCalculator\Calculator\BaseCalculator;
use EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\AfterHandleEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeHandleEvent;
use EsteIt\ShippingCalculator\Model\Package;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group unit
 */
class BaseCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchCall()
    {
        $dispatcher = \Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->shouldReceive('dispatch')
            ->times(4)
            ->getMock();

        $calculator = new BaseCalculator([
            'handler' => $this->getCalculatorHandlerMock(),
            'dispatcher' => $dispatcher,
        ]);
        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
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
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\BeforeCalculateEvent', $e);
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
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\AfterCalculateEvent', $e);
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $e->getResult());
            $this->assertSame($package, $e->getResult()->getPackage());
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertNull($e->getResult()->getError());
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
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\BeforeHandleEvent', $e);
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
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\AfterHandleEvent', $e);
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertSame($calculator->getHandler(), $e->getHandler());
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $e->getResult());
        });

        $calculator->calculate($package);
    }

    public function testExpectedException()
    {
        $handler = \Mockery::mock('EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->andThrow('EsteIt\ShippingCalculator\Exception\LogicException')
            ->getMock();

        $calculator = new BaseCalculator([
            'handler' => $handler,
        ]);

        $result = $calculator->calculate(new Package());

        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $result);
        $this->assertInstanceOf('EsteIt\ShippingCalculator\Exception\BasicExceptionInterface', $result->getError());
    }

    public function testUnexpectedException()
    {
        $this->setExpectedException('Exception');

        $handler = \Mockery::mock('EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->andThrow('Exception')
            ->getMock();

        $calculator = new BaseCalculator([
            'handler' => $handler,
        ]);

        $calculator->calculate(new Package());
    }

    /**
     * @return \Mockery\MockInterface|CalculatorHandlerInterface
     */
    public function getCalculatorHandlerMock()
    {
        return \Mockery::mock('EsteIt\ShippingCalculator\CalculatorHandler\CalculatorHandlerInterface[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->getMock();
    }
}
