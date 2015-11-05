<?php

namespace EsteIt\PackageDeliveryCalculator\Tests\DeliveryMethod;

use EsteIt\PackageDeliveryCalculator\CalculationResult;
use EsteIt\PackageDeliveryCalculator\Event\Events;
use EsteIt\PackageDeliveryCalculator\PackageDeliveryCalculator;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group unit
 */
class PackageDeliveryCalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDispatcher()
    {
        $dispatcher = new EventDispatcher();
        $calculator = new PackageDeliveryCalculator($dispatcher);

        $this->assertSame($dispatcher, $calculator->getDispatcher());
    }

    public function testAddDeliveryMethod()
    {
        $calculator = new PackageDeliveryCalculator();
        $mock = \Mockery::mock('EsteIt\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface');
        $calculator->addDeliveryMethod('test', $mock);

        $this->assertSame($calculator->getDeliveryMethod('test'), $mock);
    }

    public function testGetDeliveryMethodException()
    {
        $this->setExpectedException('EsteIt\PackageDeliveryCalculator\Exception\InvalidArgumentException', 'Delivery method was not found.');
        $calculator = new PackageDeliveryCalculator();
        $calculator->getDeliveryMethod('test');
    }

    public function testDeliveryMethodCalculate()
    {
        $calculator = new PackageDeliveryCalculator();
        $result = new CalculationResult();

        $package = \Mockery::mock('EsteIt\PackageDeliveryCalculator\Package\PackageInterface');
        $deliveryMethod = \Mockery::mock('EsteIt\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface')
            ->shouldReceive('calculate')
            ->once()
            ->andReturn($result)
            ->getMock();

        $calculator->getDispatcher()->addListener(Events::BEFORE_CALCULATE, function ($event) use($package, $deliveryMethod) {
            $this->assertInstanceOf('EsteIt\PackageDeliveryCalculator\Event\BeforeCalculateEvent', $event);
            $this->assertSame($package, $event->getPackage());
            $this->assertSame($deliveryMethod, $event->getDeliveryMethod());
        });

        $calculator->getDispatcher()->addListener(Events::AFTER_CALCULATE, function ($event) use($result) {
            $this->assertInstanceOf('EsteIt\PackageDeliveryCalculator\Event\AfterCalculateEvent', $event);
            $this->assertSame($result, $event->getResult());
        });

        $calculator->addDeliveryMethod('test', $deliveryMethod);

        $this->assertSame([$result], $calculator->calculate($package));
    }
}
