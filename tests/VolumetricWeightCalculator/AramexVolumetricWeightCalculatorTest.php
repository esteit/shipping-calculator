<?php

namespace EsteIt\ShippingCalculator\Tests\VolumetricWeightCalculator;

use EsteIt\ShippingCalculator\VolumetricWeightCalculator\AramexVolumetricWeightCalculator;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class AramexVolumetricWeightCalculatorTest extends \PHPUnit_Framework_TestCase
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
        $calculator = new AramexVolumetricWeightCalculator($math, new WeightConverter($math), new LengthConverter($math));
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
                '367.613',
            ],
            [
                $this->getFixture('dimensions_1_1_1_m'),
                '367.613',
            ],
            [
                $this->getFixture('dimensions_100_100_100_cm'),
                '367.613',
            ],
            [
                $this->getFixture('dimensions_box_39.37in'),
                '367.611',
            ],
            [
                $this->getFixture('dimensions_box_100in'),
                '6024.097',
            ],
            [
                $this->getFixture('dimensions_box_10in'),
                '6.025',
            ],
            [
                $this->getFixture('dimensions_100_10_10_in'),
                '60.241',
            ],
        ];
    }
}
