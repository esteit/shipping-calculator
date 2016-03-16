<?php

namespace EsteIt\ShippingCalculator\Tests\Tool;

use EsteIt\ShippingCalculator\Dimensions;
use EsteIt\ShippingCalculator\Tool\DimensionsNormalizer;
use Moriony\Trivial\Math\NativeMath;

/**
 * @group unit
 */
class DimensionsNormalizerTest extends \PHPUnit_Framework_TestCase
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
    public function testNormalize(Dimensions $dimensions)
    {
        $math = new NativeMath();
        $normalizer = new DimensionsNormalizer($math);
        $result = $normalizer->normalize($dimensions);

        $this->assertGreaterThanOrEqual($result->getWidth(), $result->getLength());
        $this->assertGreaterThanOrEqual($result->getHeight(), $result->getWidth());
        $this->assertSame($dimensions->getUnit(), $result->getUnit());
    }

    /**
     * @return array
     */
    public function provideDimensions()
    {
        return [
            [
                $this->getFixture('dimensions_11_10_10_in'),
            ],
            [
                $this->getFixture('dimensions_10_11_10_in'),
            ],
            [
                $this->getFixture('dimensions_10_10_11_in'),
            ],
            [
                $this->getFixture('dimensions_11_10_9_in'),
            ],
            [
                $this->getFixture('dimensions_9_11_10_in'),
            ],
            [
                $this->getFixture('dimensions_10_9_11_in'),
            ],
        ];
    }
}
