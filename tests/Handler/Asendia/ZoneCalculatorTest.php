<?php

namespace EsteIt\ShippingCalculator\Tests\Handler\Asendia;

use EsteIt\ShippingCalculator\Exception\ViolationException;
use EsteIt\ShippingCalculator\Handler\Asendia\ZoneCalculator;

/**
 * @group unit
 */
class ZoneCalculatorTest extends \PHPUnit_Framework_TestCase
{
    protected $fixtures;
    /**
     * @var ZoneCalculator
     */
    protected $zoneCalculator;

    public function setUp()
    {
        $this->fixtures = null;

        $zoneCalculator = new ZoneCalculator([
            'name' => 1,
            'weight_prices' => [
                ['weight' => 1, 'price' => 31.3],
                ['weight' => 2, 'price' => 34.90],
                ['weight' => 3, 'price' => 38.45],
                ['weight' => 4, 'price' => 42.00],
                ['weight' => 5, 'price' => 45.55],
                ['weight' => 6, 'price' => 48.84],
                ['weight' => 7, 'price' => 52.15],
                ['weight' => 8, 'price' => 55.44],
                ['weight' => 9, 'price' => 58.73],
                ['weight' => 10,'price' =>  62.03]
            ],
        ]);

        $this->zoneCalculator = $zoneCalculator;
    }

    /**
     * @dataProvider provideCalculate
     */
    public function testCalculate($weight, $expectedPrice)
    {
        $this->assertEquals($expectedPrice, $this->zoneCalculator->calculate($weight));
    }

    /**
     * @dataProvider provideCalculateException
     */
    public function testGetPriceException($exceptionMessage, $weight)
    {
        $this->setExpectedException(ViolationException::class, $exceptionMessage);
        $this->zoneCalculator->calculate($weight);
    }

    public function provideCalculate()
    {
        return [
            [0.5, 31.3],
            [4, 42.00],
            [4.5, 45.55],
            [10, 62.03],
        ];
    }

    public function provideCalculateException()
    {
        return [
            [
                'Weight should be a scalar value.',
                array(),
            ],
            [
                'Can not calculate shipping for this weight.',
                100,
            ],
            [
                'Weight should be greater than zero.',
                -10,
            ],
        ];
    }
}
