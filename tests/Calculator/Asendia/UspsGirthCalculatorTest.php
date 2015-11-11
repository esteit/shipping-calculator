<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\Asendia\UspsGirthCalculator;
use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class UspsGirthCalculatorTest extends \PHPUnit_Framework_TestCase
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
        $calculator = new UspsGirthCalculator(new NativeMath());
        $normalized = $calculator->normalizeDimensions($dimensions);

        $this->assertGreaterThanOrEqual($normalized->getHeight(), $normalized->getLength());
        $this->assertGreaterThanOrEqual($normalized->getWidth(), $normalized->getLength());
    }

    /**
     * @dataProvider provideDimensions
     */
    public function testCalculate($dimensions, $calculation)
    {
        $calculator = new UspsGirthCalculator(new NativeMath());
        $this->assertEquals($calculation, $calculator->calculate($dimensions));
    }

    /**
     * @return array
     */
    public function provideDimensions()
    {
        return [
            [
                $this->getFixture('dimensions_11_10_10_in'),
                '51',
            ],
            [
                $this->getFixture('dimensions_10_11_10_in'),
                '51',
            ],
            [
                $this->getFixture('dimensions_10_10_11_in'),
                '51',
            ],
        ];
    }
}
