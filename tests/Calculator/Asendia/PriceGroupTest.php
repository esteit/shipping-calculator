<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\Asendia\PriceGroup;

/**
 * @group unit
 */
class PriceGroupTest extends \PHPUnit_Framework_TestCase
{
    protected $fixtures;
    /**
     * @var PriceGroup
     */
    protected $priceGroup;

    public function setUp()
    {
        $this->fixtures = null;

        $priceGroup = new PriceGroup();
        $priceGroup->setPrice(1, 31.3);
        $priceGroup->setPrice(2, 34.90);
        $priceGroup->setPrice(3, 38.45);
        $priceGroup->setPrice(4, 42.00);
        $priceGroup->setPrice(5, 45.55);
        $priceGroup->setPrice(6, 48.84);
        $priceGroup->setPrice(7, 52.15);
        $priceGroup->setPrice(8, 55.44);
        $priceGroup->setPrice(9, 58.73);
        $priceGroup->setPrice(10, 62.03);
        $this->priceGroup = $priceGroup;
    }

    public function getFixture($name)
    {
        if (!$this->fixtures) {
            $loader = new \Nelmio\Alice\Fixtures\Loader();
            $this->fixtures = $loader->load(__DIR__.'/fixtures.yml');
        }
        return $this->fixtures[$name];
    }

    /**
     * @dataProvider provideGetPrice
     */
    public function testGetPrice($weight, $expectedPrice)
    {
        $this->assertEquals($expectedPrice, $this->priceGroup->getPrice($weight));
    }

    /**
     * @dataProvider provideGetPriceException
     */
    public function testGetPriceException($exceptionClass, $exceptionMessage, $weight)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);
        $this->priceGroup->getPrice($weight);
    }

    public function provideGetPrice()
    {
        return [
            [0.5, 31.3],
            [4.5, 45.55],
            [10, 62.03],
        ];
    }

    public function provideGetPriceException()
    {
        return [
            [
                'EsteIt\ShippingCalculator\Exception\InvalidWeightException',
                'Weight should be a scalar value.',
                array(),
            ],
            [
                'EsteIt\ShippingCalculator\Exception\InvalidWeightException',
                'Can not calculate shipping for this weight.',
                100,
            ],
            [
                'EsteIt\ShippingCalculator\Exception\InvalidWeightException',
                'Weight should be greater than zero.',
                -10,
            ],
        ];
    }
}
