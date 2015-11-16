<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator;

use EsteIt\ShippingCalculator\Calculator\Asendia\ZoneCalculator;
use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;

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

    public function testCalculate()
    {
        $zoneCalculator = new ZoneCalculator([
            'name' => 1,
            'weight_prices' => [
                ['weight' =>  10, 'price' => 21.40]
            ],
        ]);

        $calculator = new AsendiaCalculator([
            'zone_calculators' => [$zoneCalculator],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'fuel_subcharge' => 0.07,
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_girth' => 77.755,
        ]);
        $package = $this->getFixture('package_1');

        $result = $calculator->calculate($package);

        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $result);
        $this->assertNull($result->getError());
        $this->assertSame('22.10', $result->getTotalCost());
        $this->assertSame($calculator, $result->getCalculator());
        $this->assertSame($package, $result->getPackage());
        $this->assertSame('USD', $result->getCurrency());
    }

    /**
     * @dataProvider provideInvalidAddressesException
     */
    public function testValidateSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException', 'Can not send a package from this country.');

        $calculator = new AsendiaCalculator($calculatorOptions);
        $calculator->validateSenderAddress($address);
    }

    /**
     * @dataProvider provideInvalidAddressesException
     */
    public function testValidateRecipientSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException', 'Can not send a package to this country.');

        $calculator = new AsendiaCalculator($calculatorOptions);
        $calculator->validateRecipientAddress($address);
    }

    /**
     * @dataProvider provideValidateDimensionsException
     */
    public function testValidateDimensionsException($exceptionClass, $exceptionMessage, $calculatorOptions, $package)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $calculator = new AsendiaCalculator($calculatorOptions);
        $calculator->validateDimensions($package);
    }

    public function testValidateWeightException()
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidWeightException', 'Sender country weight limit is exceeded.');

        $calculator = new AsendiaCalculator([
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'fuel_subcharge' => 0.07,
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_girth' => 77.755,
        ]);

        $calculator->validateWeight($this->getFixture('package_3'));
    }

    /**
     * @return array
     */
    public function provideInvalidAddressesException()
    {
        $calculatorOptions = [
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'fuel_subcharge' => 0.07,
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_girth' => 77.755,
        ];

        return [
            [
                $calculatorOptions,
                $this->getFixture('french_address'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('russian_address'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidateDimensionsException()
    {
        $calculatorOptions = [
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'fuel_subcharge' => 0.07,
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 10,
            'maximum_girth' => 10,
        ];

        return [
            [
                'EsteIt\ShippingCalculator\Exception\InvalidDimensionsException',
                'Side length limit is exceeded.',
                $calculatorOptions,
                $this->getFixture('package_2'),
            ],
            [
                'EsteIt\ShippingCalculator\Exception\InvalidDimensionsException',
                'Girth limit is exceeded.',
                $calculatorOptions,
                $this->getFixture('package_1'),
            ],
        ];
    }
}
