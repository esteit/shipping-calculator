<?php

namespace Rage\PackageDeliveryCalculator\Tests\DeliveryMethod;

use Rage\PackageDeliveryCalculator\CalculationResult;
use Rage\PackageDeliveryCalculator\Event\Events;
use Rage\PackageDeliveryCalculator\PackageDeliveryCalculator;
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
        $mock = \Mockery::mock('Rage\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface');
        $calculator->addDeliveryMethod('test', $mock);

        $this->assertSame($calculator->getDeliveryMethod('test'), $mock);
    }

    public function testGetDeliveryMethodException()
    {
        $this->setExpectedException('Rage\PackageDeliveryCalculator\Exception\InvalidArgumentException', 'Delivery method was not found.');
        $calculator = new PackageDeliveryCalculator();
        $calculator->getDeliveryMethod('test');
    }

    public function testDeliveryMethodCalculate()
    {
        $calculator = new PackageDeliveryCalculator();
        $result = new CalculationResult();

        $package = \Mockery::mock('Rage\PackageDeliveryCalculator\Package\PackageInterface');
        $deliveryMethod = \Mockery::mock('Rage\PackageDeliveryCalculator\DeliveryMethod\DeliveryMethodInterface')
            ->shouldReceive('calculate')
            ->once()
            ->andReturn($result)
            ->getMock();

        $calculator->getDispatcher()->addListener(Events::BEFORE_CALCULATE, function ($event) use($package, $deliveryMethod) {
            $this->assertInstanceOf('Rage\PackageDeliveryCalculator\Event\BeforeCalculateEvent', $event);
            $this->assertSame($package, $event->getPackage());
            $this->assertSame($deliveryMethod, $event->getDeliveryMethod());
        });

        $calculator->getDispatcher()->addListener(Events::AFTER_CALCULATE, function ($event) use($result) {
            $this->assertInstanceOf('Rage\PackageDeliveryCalculator\Event\AfterCalculateEvent', $event);
            $this->assertSame($result, $event->getResult());
        });

        $calculator->addDeliveryMethod('test', $deliveryMethod);

        $this->assertSame([$result], $calculator->calculate($package));
    }
}
