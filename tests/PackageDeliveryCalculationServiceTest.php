<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\CalculationResult;
use EsteIt\ShippingCalculator\Event\Events;
use EsteIt\ShippingCalculator\PackageDeliveryCalculationService;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group unit
 */
class PackageDeliveryCalculationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $calculator = new PackageDeliveryCalculationService($dispatcher);

        $this->assertSame($dispatcher, $calculator->getDispatcher());
    }

    public function testAddCalculator()
    {
        $calculator = new PackageDeliveryCalculationService();
        $mock = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\CalculatorInterface');
        $calculator->addCalculator('test', $mock);

        $this->assertSame($calculator->getCalculator('test'), $mock);
    }

    public function testGetCalculatorException()
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidArgumentException', 'Delivery method was not found.');
        $calculator = new PackageDeliveryCalculationService();
        $calculator->getCalculator('test');
    }

    public function testCalculate()
    {
        $service = new PackageDeliveryCalculationService();
        $result = new CalculationResult();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Package\PackageInterface');
        $calculator = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\CalculatorInterface')
            ->shouldReceive('calculate')
            ->once()
            ->andReturn($result)
            ->getMock();

        $service->getDispatcher()->addListener(Events::BEFORE_CALCULATE, function ($event) use($package, $calculator) {
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\BeforeCalculateEvent', $event);
            $this->assertSame($package, $event->getPackage());
            $this->assertSame($calculator, $event->getCalculator());
        });

        $service->getDispatcher()->addListener(Events::AFTER_CALCULATE, function ($event) use($result) {
            $this->assertInstanceOf('EsteIt\ShippingCalculator\Event\AfterCalculateEvent', $event);
            $this->assertSame($result, $event->getResult());
        });

        $service->addCalculator('test', $calculator);

        $this->assertSame([$result], $service->calculate($package));
    }
}
