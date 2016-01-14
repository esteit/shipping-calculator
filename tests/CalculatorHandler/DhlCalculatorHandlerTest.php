<?php

namespace EsteIt\ShippingCalculator\Tests\CalculatorHandler;

use EsteIt\ShippingCalculator\CalculatorHandler\DhlCalculatorHandler;
use EsteIt\ShippingCalculator\CalculatorHandler\Dhl\ZoneCalculator;
use EsteIt\ShippingCalculator\Model\CalculationResult;

/**
 * @group unit
 */
class DhlCalculatorHandlerTest extends \PHPUnit_Framework_TestCase
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

    public function testCreate()
    {
        $calculator = DhlCalculatorHandler::create([
            'zone_calculators' => [
                [
                    'name' => 1,
                    'weight_prices' => [
                        ['weight' =>  10, 'price' => 21.40],
                    ],
                ],
            ],
            'import_countries' => [
                ['code' => 'USA', 'zone' => 1],
            ],
            'export_countries' => [
                ['code' => 'USA'],
            ],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [
                'length' => 40,
                'width' => 40,
                'height' => 40
            ],
            'maximum_weight' => 60,
        ]);

        $this->assertInstanceOf('EsteIt\ShippingCalculator\CalculatorHandler\DhlCalculatorHandler', $calculator);
    }

    /**
     * @dataProvider provideVisit
     */
    public function testVisit($package, $expectedCost)
    {
        $zoneCalculator = new ZoneCalculator([
            'name' => 1,
            'weight_prices' => [
                ['weight' =>  10, 'price' => 21.40],
                ['weight' =>  1000, 'price' => 42.80],
            ],
        ]);

        $calculator = new DhlCalculatorHandler([
            'zone_calculators' => [$zoneCalculator],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [40, 40, 40],
            'maximum_weight' => 60,
        ]);
        $result = new CalculationResult();
        $calculator->visit($result, $package);

        $this->assertInstanceOf('EsteIt\ShippingCalculator\Model\CalculationResultInterface', $result);
        $this->assertNull($result->getError());
        $this->assertSame($expectedCost, $result->getShippingCost());
        $this->assertSame('USD', $result->getCurrency());
    }

    /**
     * @dataProvider provideInvalidSenderAddresses
     */
    public function testValidateSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException', 'Can not send a package from this country.');

        $calculator = new DhlCalculatorHandler($calculatorOptions);
        $calculator->validateSenderAddress($address);
    }

    /**
     * @dataProvider provideInvalidRecipientAddresses
     */
    public function testValidateRecipientSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException', 'Can not send a package to this country.');

        $calculator = new DhlCalculatorHandler($calculatorOptions);
        $calculator->validateRecipientAddress($address);
    }

    /**
     * @dataProvider provideValidateDimensionsException
     */
    public function testValidateDimensionsException($exceptionMessage, $package)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidDimensionsException', $exceptionMessage);

        $calculator = new DhlCalculatorHandler([
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [20, 10, 10],
            'maximum_weight' => 60,
        ]);
        $calculator->validateDimensions($package);
    }

    public function testValidateWeightException()
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidWeightException', 'Sender country weight limit is exceeded.');

        $calculator = new DhlCalculatorHandler([
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [41.338, 10, 10],
            'maximum_weight' => 10,
        ]);

        $calculator->validateWeight($this->getFixture('package_3'));
    }

    /**
     * @return array
     */
    public function provideInvalidSenderAddresses()
    {
        $calculatorOptions = [
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [41.338, 10, 10],
            'maximum_weight' => 60,
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
    public function provideInvalidRecipientAddresses()
    {
        $calculatorOptions = [
            'zone_calculators' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [41.338, 10, 10],
            'maximum_weight' => 60,
        ];

        return [
            [
                $calculatorOptions,
                $this->getFixture('french_address'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('usa_address'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidateDimensionsException()
    {
        return [
            [
                'Dimensions must be greater than zero.',
                $this->getFixture('package_7'),
            ],
            [
                'Dimensions limit is exceeded.',
                $this->getFixture('package_2'),
            ],
            [
                'Dimensions limit is exceeded.',
                $this->getFixture('package_4'),
            ],
            [
                'Dimensions limit is exceeded.',
                $this->getFixture('package_8'),
            ],
            [
                'Dimensions limit is exceeded.',
                $this->getFixture('package_5'),
            ],
        ];
    }

    public function provideVisit()
    {
        return [
            [
                $this->getFixture('package_1'),
                21.4,
            ],
            [
                $this->getFixture('package_5'),
                42.8,
            ],
        ];
    }
}
