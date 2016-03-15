<?php

namespace EsteIt\ShippingCalculator\Tests\Handler;

use EsteIt\ShippingCalculator\Handler\IParcelHandler;
use EsteIt\ShippingCalculator\Model\CalculationResult;
use EsteIt\ShippingCalculator\Result;

/**
 * @group unit
 */
class IParcelHandlerTest extends \PHPUnit_Framework_TestCase
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
        $calculator = IParcelHandler::create([
            'zones' => [
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
            'maximum_dimension' => 41.338,
            'maximum_perimeter' => 300,
            'maximum_weight' => 60,
        ]);

        $this->assertInstanceOf(IParcelHandler::class, $calculator);
    }

    /**
     * @dataProvider provideVisit
     */
    public function testVisit($package, $expectedCost)
    {
        $calculator = new IParcelHandler([
            'zones' => [
                [
                    'name' => 1,
                    'weight_prices' => [
                        ['weight' =>  10, 'price' => 21.40],
                        ['weight' =>  1000, 'price' => 42.80],
                    ],
                ]
            ],
            'import_countries' => [
                $this->getFixture('import_country_usa')
            ],
            'export_countries' => [
                $this->getFixture('export_country_usa')
            ],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_perimeter' => 300,
            'maximum_weight' => 60,
        ]);

        $result = new Result();
        $calculator->calculate($result, $package);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEmpty($result->getViolations());
        $this->assertSame($expectedCost, $result->get('shipping_cost'));
    }

    /**
     * @dataProvider provideGetPriceException
     */
    public function testGetPriceInvalidWeightException($exceptionMessage, $exceptionClass, $calculatorOptions, $package)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->getPrice($package);
    }

    /**
     * @dataProvider provideInvalidSenderAddressException
     */
    public function testValidateSenderAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException', 'Can not send a package from this country.');

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->validateSenderAddress($address);
    }

    /**
     * @dataProvider provideInvalidRecipientAddressException
     */
    public function testValidateRecipientAddressException($calculatorOptions, $address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException', 'Can not send a package to this country.');

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->validateRecipientAddress($address);
    }

    /**
     * @dataProvider provideValidateDimensionsException
     */
    public function testValidateMaximumDimensionException($calculatorOptions, $package)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidDimensionsException', 'Side length limit is exceeded.');

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->validateMaximumDimension($package);
    }

    /**
     * @dataProvider provideValidateMaximumPerimeterException
     */
    public function testValidateMaximumPerimeterException($calculatorOptions, $package)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidDimensionsException', 'Maximum perimeter limit is exceeded.');

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->validateMaximumPerimeter($package);
    }

    /**
     * @dataProvider provideValidateWeightException
     */
    public function testValidateWeightException($exceptionMessage, $calculatorOptions, $package)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidWeightException', $exceptionMessage);

        $calculator = new IParcelHandler($calculatorOptions);
        $calculator->validateWeight($package);
    }

    /**
     * @return array
     */
    public function provideInvalidSenderAddressException()
    {
        $calculatorOptions = [
            'zones' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_perimeter' => 30,
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
    public function provideInvalidRecipientAddressException()
    {
        $calculatorOptions = [
            'zones' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 41.338,
            'maximum_perimeter' => 30,
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
        $calculatorOptions = [
            'zones' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 10,
            'maximum_perimeter' => 10,
            'maximum_weight' => 60,
        ];

        return [
            [
                $calculatorOptions,
                $this->getFixture('package_2'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('package_4'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('package_5'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidateMaximumPerimeterException()
    {
        $calculatorOptions = [
            'zones' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 300,
            'maximum_perimeter' => 10,
            'maximum_weight' => 60,
        ];

        return [
            [
                $calculatorOptions,
                $this->getFixture('package_2'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('package_4'),
            ],
            [
                $calculatorOptions,
                $this->getFixture('package_5'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function provideValidateWeightException()
    {
        $calculatorOptions = [
            'zones' => [],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 100,
            'maximum_perimeter' => 300,
            'maximum_weight' => 10,
        ];

        return [
            [
                'Sender country weight limit is exceeded.',
                $calculatorOptions,
                $this->getFixture('package_3')
            ],
            [
                'Weight should be greater than zero.',
                $calculatorOptions,
                $this->getFixture('package_6')
            ],
        ];
    }

    public function provideGetPriceException()
    {
        $calculatorOptions = [
            'zones' => [
                [
                    'name' => 1,
                    'weight_prices' => [],
                ]
            ],
            'import_countries' => [$this->getFixture('import_country_usa')],
            'export_countries' => [$this->getFixture('export_country_usa')],
            'mass_unit' => 'lb',
            'dimensions_unit' => 'in',
            'maximum_dimension' => 100,
            'maximum_perimeter' => 300,
            'maximum_weight' => 10,
        ];

        return [
            [
                'Can not calculate shipping for this weight.',
                'EsteIt\ShippingCalculator\Exception\InvalidWeightException',
                $calculatorOptions,
                $this->getFixture('package_3')
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
