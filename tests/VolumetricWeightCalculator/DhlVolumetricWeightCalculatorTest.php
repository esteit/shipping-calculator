<?php

namespace EsteIt\ShippingCalculator\Tests\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\VolumetricWeightCalculator\DhlVolumetricWeightCalculator;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class DhlVolumetricWeightCalculatorTest extends \PHPUnit_Framework_TestCase
{
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = null;
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
     * @dataProvider provideCalculate
     */
    public function testCalculate($dimensions, $expectedVolumeWeight, $weightUnit)
    {
        $math = new NativeMath();
        $calculator = new DhlVolumetricWeightCalculator($math, new WeightConverter($math), new LengthConverter($math));
        $volumetricWeight = $calculator->calculate($dimensions, $weightUnit);
        $this->assertEquals($expectedVolumeWeight, $volumetricWeight);
    }

    /**
     * @return array
     */
    public function provideCalculate()
    {
        return [
            [
                $this->getFixture('dimensions_1_1_1_m'),
                '200',
                'kg',
            ],
            [
                $this->getFixture('dimensions_100_100_100_cm'),
                '200',
                'kg',
            ],
            [
                $this->getFixture('dimensions_box_39.37in'),
                '199.999',
                'kg',
            ],
            [
                $this->getFixture('dimensions_1_1_1_m'),
                '440.925',
                'lb',
            ],
            [
                $this->getFixture('dimensions_100_100_100_cm'),
                '440.925',
                'lb',
            ],
            [
                $this->getFixture('dimensions_box_39.37in'),
                '440.922',
                'lb',
            ],
            [
                $this->getFixture('dimensions_box_100in'),
                '7225.46',
                'lb',
            ],
            [
                $this->getFixture('dimensions_box_10in'),
                '7.226',
                'lb',
            ],
            [
                $this->getFixture('dimensions_100_10_10_in'),
                '72.255',
                'lb',
            ],
        ];
    }
}
