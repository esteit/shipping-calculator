<?php

namespace EsteIt\ShippingCalculator\Tests\Tool;

use EsteIt\ShippingCalculator\Tool\MaximumPerimeterCalculator;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class MaximumPerimeterCalculatorTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider provideDimensions
     */
    public function testNormalizeDimensions($dimensions)
    {
        $calculator = new MaximumPerimeterCalculator(new NativeMath());
        $normalized = $calculator->normalizeDimensions($dimensions);

        $this->assertGreaterThanOrEqual($normalized->getHeight(), $normalized->getLength());
        $this->assertGreaterThanOrEqual($normalized->getWidth(), $normalized->getLength());
    }

    /**
     * @dataProvider provideDimensions
     */
    public function testCalculate($dimensions, $calculation)
    {
        $calculator = new MaximumPerimeterCalculator(new NativeMath());
        $girth = $calculator->calculate($dimensions);
        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\Length', $girth);
        $this->assertEquals($calculation, $girth->getValue());
    }

    /**
     * @return array
     */
    public function provideDimensions()
    {
        return [
            [
                $this->getFixture('dimensions_11_10_10_in'),
                '42',
            ],
            [
                $this->getFixture('dimensions_10_11_10_in'),
                '42',
            ],
            [
                $this->getFixture('dimensions_10_10_11_in'),
                '42',
            ],
        ];
    }
}
