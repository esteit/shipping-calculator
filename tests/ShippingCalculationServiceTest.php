<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\ShippingCalculationService;

/**
 * @group unit
 */
class ShippingCalculationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testAddCalculator()
    {
        $calculator = new ShippingCalculationService();
        $mock = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator');
        $calculator->addCalculator('test', $mock);

        $this->assertSame($calculator->getCalculator('test'), $mock);
    }

    public function testGetCalculatorException()
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidArgumentException', 'Calculator was not found.');
        $calculator = new ShippingCalculationService();
        $calculator->getCalculator('test');
    }

    public function testCalculate()
    {
        $service = new ShippingCalculationService();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
        $calculator = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->once()
            ->getMock();

        $service->addCalculator('test', $calculator);

        $result = $service->calculate($package);
        $this->assertCount(1, $result);
        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', reset($result));
    }
}
