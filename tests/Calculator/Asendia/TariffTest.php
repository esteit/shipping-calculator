<?php

namespace EsteIt\ShippingCalculator\Tests\Calculator\Asendia;

use EsteIt\ShippingCalculator\Calculator\Asendia\RecipientCountry;
use EsteIt\ShippingCalculator\Calculator\Asendia\Tariff;
use EsteIt\ShippingCalculator\Model\Package;
use EsteIt\ShippingCalculator\Model\Weight;

/**
 * @group unit
 */
class TariffTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider provideInvalidAddressesException
     */
    public function testValidateSenderAddressException($address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException', 'Can not send a package from this country.');

        $tariff = new Tariff();
        $tariff->validateSenderAddress($address);
    }

    /**
     * @dataProvider provideInvalidAddressesException
     */
    public function testValidateRecipientSenderAddressException($address)
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException', 'Can not send a package to this country.');

        $recipientCountry = new RecipientCountry();
        $recipientCountry->setCode('RUS');
        $recipientCountry->setPriceGroup(1);
        $recipientCountry->setWeightLimit(10);

        $tariff = new Tariff();
        $tariff->addRecipientCountry($recipientCountry);
        $tariff->validateRecipientAddress($address);
    }

    /**
     * @dataProvider provideValidateDimensionsException
     */
    public function testValidateDimensionsException($exceptionClass, $exceptionMessage, $dimensions)
    {
        $this->setExpectedException($exceptionClass, $exceptionMessage);

        $tariff = new Tariff();
        $tariff->setSideLengthLimit('10');
        $tariff->setGirthLimit('10');
        $tariff->setDimensionsUnit('in');

        $package = new Package();
        $package->setDimensions($dimensions);
        $package->setWeight(new Weight());

        $tariff->validateDimensions($package);
    }

    public function testValidateWeightException()
    {
        $this->setExpectedException('EsteIt\ShippingCalculator\Exception\InvalidWeightException', 'Sender country weight limit is exceeded.');

        $recipientCountry = new RecipientCountry();
        $recipientCountry->setCode('RUS');
        $recipientCountry->setWeightLimit(5);

        $tariff = new Tariff();
        $tariff->setMassUnit('lb');
        $tariff->addRecipientCountry($recipientCountry);

        $tariff->validateWeight($this->getFixture('package_1'));
    }

    /**
     * @return array
     */
    public function provideInvalidAddressesException()
    {
        return [
            [
                $this->getFixture('french_address'),
            ],
            [
                $this->getFixture('russian_address'),
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
                'EsteIt\ShippingCalculator\Exception\InvalidDimensionsException',
                'Side length limit is exceeded.',
                $this->getFixture('dimensions_11_10_10_in'),
            ],
            [
                'EsteIt\ShippingCalculator\Exception\InvalidDimensionsException',
                'Girth limit is exceeded.',
                $this->getFixture('dimensions_10_10_10_in'),
            ],
        ];
    }
}
