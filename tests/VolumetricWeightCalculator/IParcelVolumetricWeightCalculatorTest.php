<?php

namespace EsteIt\ShippingCalculator\Tests\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\VolumetricWeightCalculator\IParcelVolumetricWeightCalculator;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class IParcelVolumetricWeightCalculatorTest extends \PHPUnit_Framework_TestCase
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
    public function testCalculate($dimensions, $expectedVolumeWeight)
    {
        $math = new NativeMath();
        $calculator = new IParcelVolumetricWeightCalculator($math, new WeightConverter($math), new LengthConverter($math));
        $volumetricWeight = $calculator->calculate($dimensions);

        $this->assertEquals($expectedVolumeWeight, $volumetricWeight->getValue());
        $this->assertEquals('lb', $volumetricWeight->getUnit());
    }

    /**
     * @return array
     */
    public function provideCalculate()
    {
        return [
            [
                $this->getFixture('dimensions_1_1_1_m'),
                '439.020',
            ],
            [
                $this->getFixture('dimensions_1_1_1_m'),
                '439.020',
            ],
            [
                $this->getFixture('dimensions_100_100_100_cm'),
                '439.020',
            ],
            [
                $this->getFixture('dimensions_box_39.37in'),
                '439.018',
            ],
            [
                $this->getFixture('dimensions_box_100in'),
                '7194.245',
            ],
            [
                $this->getFixture('dimensions_box_10in'),
                '7.195',
            ],
            [
                $this->getFixture('dimensions_100_10_10_in'),
                '71.943',
            ],
        ];
    }
}
