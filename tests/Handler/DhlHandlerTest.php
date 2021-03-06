<?php

namespace EsteIt\ShippingCalculator\Tests\Handler;

use EsteIt\ShippingCalculator\Exception\ViolationException;
use EsteIt\ShippingCalculator\Handler\DhlHandler;
use EsteIt\ShippingCalculator\Handler\Dhl\ZoneCalculator;
use EsteIt\ShippingCalculator\Result;

/**
 * @group unit
 */
class DhlHandlerTest extends \PHPUnit_Framework_TestCase
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
        $calculator = DhlHandler::create([
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

        $this->assertInstanceOf(DhlHandler::class, $calculator);
    }

    /**
     * @dataProvider provideVisit
     */
    public function testCalculate($package, $expectedCost)
    {
        $zoneCalculator = new ZoneCalculator([
            'name' => 1,
            'weight_prices' => [
                ['weight' =>  10, 'price' => 21.40],
                ['weight' =>  1000, 'price' => 42.80],
            ],
        ]);

        $calculator = new DhlHandler([
            'zone_calculators' => [$zoneCalculator],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimensions' => [40, 40, 40],
            'maximum_weight' => 60,
        ]);
        $result = new Result();
        $calculator->calculate($result, $package);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEmpty($result->getViolations());
        $this->assertSame($expectedCost, $result->get('shipping_cost'));
    }

    /**
     * @dataProvider provideInvalidSenderAddresses
     */
    public function testValidateSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException(ViolationException::class, 'Can not send a package from this country.');

        $calculator = new DhlHandler($calculatorOptions);
        $calculator->validateSenderAddress($address);
    }

    /**
     * @dataProvider provideInvalidRecipientAddresses
     */
    public function testValidateRecipientSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException(ViolationException::class, 'Can not send a package to this country.');

        $calculator = new DhlHandler($calculatorOptions);
        $calculator->validateRecipientAddress($address);
    }

    /**
     * @dataProvider provideValidateDimensionsException
     */
    public function testValidateDimensionsException($exceptionMessage, $package)
    {
        $this->setExpectedException(ViolationException::class, $exceptionMessage);

        $calculator = new DhlHandler([
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
        $this->setExpectedException(ViolationException::class, 'Sender country weight limit is exceeded.');

        $calculator = new DhlHandler([
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
