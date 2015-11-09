<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\AbstractCalculator;
use EsteIt\ShippingCalculator\Event\AfterCalculateEvent;
use EsteIt\ShippingCalculator\Event\BeforeCalculateEvent;

/**
 * @group unit
 */
class AbstractCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchCall()
    {
        $dispatcher = \Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->shouldReceive('dispatch')
            ->twice()
            ->getMock();

        $calculator = $this->getCalculatorMock();
        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
        $calculator->setDispatcher($dispatcher);
        $calculator->calculate($package);
    }

    public function testBeforeCalculateEvent()
    {
        $calculator = $this->getCalculatorMock();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');

        $calculator->getDispatcher()->addListener('before_calculate', function ($e) use ($calculator, $package) {
            /** @var BeforeCalculateEvent $e */
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\BeforeCalculateEvent', $e);
            $this->assertSame($calculator, $e->getCalculator());
            $this->assertSame($package, $e->getPackage());
        });

        $calculator->calculate($package);
    }

    public function testAfterCalculateEvent()
    {
        $calculator = $this->getCalculatorMock();
        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');

        $calculator->getDispatcher()->addListener('after_calculate', function ($e) use ($calculator, $package) {
            /** @var AfterCalculateEvent $e */
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\AfterCalculateEvent', $e);
            $result = $e->getResult();
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $result);
            $this->assertSame($package, $result->getPackage());
            $this->assertSame($calculator, $result->getCalculator());
            $this->assertNull($result->getError());
        });

        $calculator->calculate($package);
    }

    public function testExpectedException()
    {
        $calculator = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->andThrow('EsteIt\ShippingCalculator\Exception\LogicException')
            ->getMock();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
        $result = $calculator->calculate($package);

        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $result);
        $this->assertInstanceOf('EsteIt\ShippingCalculator\Exception\BasicExceptionInterface', $result->getError());
    }

    public function testUnexpectedException()
    {
        $this->setExpectedException('Exception');

        $calculator = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->andThrow('Exception')
            ->getMock();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
        $calculator->calculate($package);
    }

    /**
     * @return \Mockery\MockInterface|AbstractCalculator
     */
    public function getCalculatorMock()
    {
        return \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->once()
            ->getMock();
    }
}
