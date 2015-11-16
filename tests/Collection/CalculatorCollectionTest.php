<?php

namespace EsteIt\ShippingCalculator\Tests\Collection;

use EsteIt\ShippingCalculator\Collection\CalculatorCollection;

/**
 * @group unit
 */
class CalculatorCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCalculate()
    {
        $collection = new CalculatorCollection();

        $package = \Mockery::mock('EsteIt\ShippingCalculator\Model\PackageInterface');
        $calculator = \Mockery::mock('EsteIt\ShippingCalculator\Calculator\AbstractCalculator[visit]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('visit')
            ->once()
            ->getMock();

        $collection->set('test', $calculator);

        $result = $collection->calculate($package);
        $this->assertCount(1, $result);
        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', reset($result));
    }
}
