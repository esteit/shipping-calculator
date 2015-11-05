<?php

namespace EsteIt\PackageDeliveryCalculator\Tests\Calculator;

use EsteIt\PackageDeliveryCalculator\Calculator\AsendiaCalculator;

/**
 * @group unit
 */
class AsendiaCalculatorTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider provideGetTariff
     */
    public function testGetTariff($options)
    {
        $method = new AsendiaCalculator($options);

        $now = new \DateTime();
        $tariff = $method->getTariff($now);
        $this->assertInstanceOf('EsteIt\PackageDeliveryCalculator\Calculator\Asendia\Tariff', $tariff);

        $this->assertLessThanOrEqual($now, $tariff->getDate());
        $this->assertEquals('0.07', $tariff->getFuelSubcharge());
    }

    /**
     * @dataProvider provideGetTariffException
     */
    public function testGetTariffException($exceptionClass, $exceptionMessage)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $method = new AsendiaCalculator();
        $method->getTariff(new \DateTime());
    }

    public function testCalculate()
    {
        $deliveryMethod = new AsendiaCalculator();
        $deliveryMethod->addTariff($this->getFixture('tariff_1'));
        $package = $this->getFixture('base_package_1');

        $result = $deliveryMethod->calculate($package);

        $this->assertInstanceOf('EsteIt\PackageDeliveryCalculator\CalculationResult', $result);
        $this->assertSame('22.10', $result->getTotalCost());
        $this->assertSame($deliveryMethod, $result->getCalculator());
        $this->assertSame($package, $result->getPackage());
    }

    /**
     * @return array
     */
    public function provideGetTariff()
    {
        return [
            [
                // One tariff
                [
                    'tariffs' => [
                        $this->getFixture('tariff_1'),
                    ],
                ],
                // Two tariffs with one date
                [
                    'tariffs' => [
                        $this->getFixture('tariff_2'),
                        $this->getFixture('tariff_3'),
                    ],
                ],
                // Two tariffs with different dates
                [
                    'tariffs' => [
                        $this->getFixture('tariff_4'),
                        $this->getFixture('tariff_5'),
                    ],
                ],
                // Three tariffs with different dates
                [
                    'tariffs' => [
                        $this->getFixture('tariff_6'),
                        $this->getFixture('tariff_7'),
                        $this->getFixture('tariff_8'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideGetTariffException()
    {
        return [
            [
                'EsteIt\PackageDeliveryCalculator\Exception\LogicException',
                'Tariff was not found.',
            ],
        ];
    }
}