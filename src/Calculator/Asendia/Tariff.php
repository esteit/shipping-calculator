<?php

namespace EsteIt\ShippingCalculator\Calculator\Asendia;

use EsteIt\ShippingCalculator\Exception\InvalidConfigurationException;
use EsteIt\ShippingCalculator\Exception\InvalidDimensionsException;
use EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Exception\InvalidWeightException;
use EsteIt\ShippingCalculator\Model\AddressInterface;
use EsteIt\ShippingCalculator\Model\DimensionsInterface;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\UnitConverterInterface;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\NativeMath;

/**
 * Class Tariff
 */
class Tariff
{
    /**
     * @var PriceGroup[]
     */
    protected $priceGroups;
    /**
     * @var string|float|int
     */
    protected $fuelSubcharge;
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var RecipientCountry[]
     */
    protected $recipientCountries;
    /**
     * @var string[]
     */
    protected $senderCountries;
    /**
     * @var MathInterface
     */
    protected $math;
    /**
     * @var UnitConverterInterface
     */
    protected $weightConverter;
    /**
     * @var UnitConverterInterface
     */
    protected $lengthConverter;
    /**
     * @var UspsGirthCalculator
     */
    protected $girthCalculator;
    /**
     * @var string
     */
    protected $massUnit;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $dimensionsUnit;
    /**
     * @var string
     */
    protected $sideLengthLimit;
    /**
     * @var string
     */
    protected $girthLimit;

    /**
     * Tariff constructor.
     */
    public function __construct()
    {
        $this->senderCountries = ['USA'];
        $this->recipientCountries = [];
        $this->priceGroups = [];
        $this->currency = 'USD';
    }

    /**
     * @param PackageInterface $package
     * @return mixed
     */
    public function calculate(PackageInterface $package)
    {
        $this->validateSenderAddress($package->getSenderAddress());
        $this->validateRecipientAddress($package->getRecipientAddress());
        $this->validateDimensions($package->getDimensions());
        $this->validateWeight($package);

        $priceGroup = $this->getPriceGroup($package);

        $weight = $package->getWeight();
        $weight = $this->getWeightConverter()->convert($weight->getValue(), $weight->getUnit(), $this->getMassUnit());

        $math = $this->getMath();
        $cost = $priceGroup->getPrice($weight);
        $wholeWeight = $math->roundDown($weight);
        $fuelCost = $math->mul($wholeWeight, $this->getFuelSubcharge());
        $total = $math->sum($cost, $fuelCost);
        $total = $math->roundUp($total, 2);
        $result = number_format($total, 2, '.', '');

        return $result;
    }

    /**
     * @param AddressInterface $address
     */
    public function validateSenderAddress(AddressInterface $address)
    {
        $isValidSenderCountry = in_array($address->getCountryCode(), $this->senderCountries);
        if (!$isValidSenderCountry) {
            throw new InvalidSenderAddressException('Can not send a package from this country.');
        }
    }

    /**
     * @param AddressInterface $address
     */
    public function validateRecipientAddress(AddressInterface $address)
    {
        try {
            $recipientCountry = $this->getRecipientCountry($address->getCountryCode());
        } catch (InvalidArgumentException $e) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }

        if (!array_key_exists($recipientCountry->getPriceGroup(), $this->priceGroups)) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }
    }

    /**
     * @param DimensionsInterface $dimensions
     */
    public function validateDimensions(DimensionsInterface $dimensions)
    {
        $math = $this->getMath();
        $converter = $this->getLengthConverter();
        $girthCalculator = $this->getGirthCalculator();

        $dimensions = $girthCalculator->normalizeDimensions($dimensions);
        $sideLengthLimit = $converter->convert($this->getSideLengthLimit(), $this->getDimensionsUnit(), $dimensions->getUnit());
        if ($math->greaterThan($dimensions->getLength(), $sideLengthLimit)) {
            throw new InvalidDimensionsException('Side length limit is exceeded.');
        }

        $girth = $girthCalculator->calculate($dimensions);
        $girthLimit = $converter->convert($this->getGirthLimit(), $this->getDimensionsUnit(), $dimensions->getUnit());
        if ($math->greaterThan($girth, $girthLimit)) {
            throw new InvalidDimensionsException('Girth limit is exceeded.');
        }
    }

    public function validateWeight(PackageInterface $package)
    {
        $math = $this->getMath();
        $converter = $this->getWeightConverter();
        $country = $this->getRecipientCountry($package->getRecipientAddress()->getCountryCode());

        $countryWeightLimit = $converter->convert($country->getWeightLimit(), $this->getMassUnit(), $package->getWeight()->getUnit());
        if ($math->greaterThan($package->getWeight()->getValue(), $countryWeightLimit)) {
            throw new InvalidWeightException('Sender country weight limit is exceeded.');
        }
    }

    /**
     * @param PriceGroup $priceGroup
     * @return $this
     */
    public function addPriceGroup(PriceGroup $priceGroup)
    {
        $this->priceGroups[$priceGroup->getName()] = $priceGroup;

        return $this;
    }

    /**
     * @param PriceGroup[] $priceGroups
     * @return $this
     */
    public function addPriceGroups(array $priceGroups)
    {
        foreach ($priceGroups as $priceGroup) {
            $this->addPriceGroup($priceGroup);
        }

        return $this;
    }

    /**
     * @param PackageInterface $package
     * @return PriceGroup
     */
    public function getPriceGroup($package)
    {
        $country = $this->getRecipientCountry($package->getRecipientAddress()->getCountryCode());

        if (!array_key_exists($country->getPriceGroup(), $this->priceGroups)) {
            throw new InvalidConfigurationException('Price group does not exist.');
        }

        return $this->priceGroups[$country->getPriceGroup()];
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return float|int|string
     */
    public function getFuelSubcharge()
    {
        return $this->fuelSubcharge;
    }

    /**
     * @param float|int|string $fuelSubcharge
     * @return $this
     */
    public function setFuelSubcharge($fuelSubcharge)
    {
        $this->fuelSubcharge = $fuelSubcharge;

        return $this;
    }

    /**
     * @return RecipientCountry[]
     */
    public function getRecipientCountries()
    {
        return $this->recipientCountries;
    }

    /**
     * @param RecipientCountry $country
     * @return $this
     */
    public function addRecipientCountry(RecipientCountry $country)
    {
        $this->recipientCountries[$country->getCode()] = $country;

        return $this;
    }

    /**
     * @param RecipientCountry[] $countries
     * @return $this
     */
    public function addRecipientCountries(array $countries)
    {
        foreach ($countries as $country) {
            $this->addRecipientCountry($country);
        }

        return $this;
    }

    /**
     * @param string $code
     * @return RecipientCountry
     */
    public function getRecipientCountry($code)
    {
        if (!array_key_exists($code, $this->recipientCountries)) {
            throw new InvalidArgumentException();
        }

        return $this->recipientCountries[$code];
    }

    /**
     * @return MathInterface
     */
    public function getMath()
    {
        if (!$this->math) {
            $this->math = new NativeMath();
        }

        return $this->math;
    }

    /**
     * @param MathInterface $math
     * @return $this
     */
    public function setMath(MathInterface $math)
    {
        $this->math = $math;

        return $this;
    }

    /**
     * @return UnitConverterInterface
     */
    public function getWeightConverter()
    {
        if (!$this->weightConverter) {
            $this->weightConverter = new WeightConverter($this->getMath());
        }

        return $this->weightConverter;
    }

    /**
     * @param UnitConverterInterface $converter
     * @return $this
     */
    public function setWeightConverter(UnitConverterInterface $converter)
    {
        $this->weightConverter = $converter;

        return $this;
    }

    /**
     * @return UnitConverterInterface
     */
    public function getLengthConverter()
    {
        if (!$this->lengthConverter) {
            $this->lengthConverter = new LengthConverter($this->getMath());
        }

        return $this->lengthConverter;
    }

    /**
     * @param UnitConverterInterface $converter
     * @return $this
     */
    public function setLengthConverter(UnitConverterInterface $converter)
    {
        $this->lengthConverter = $converter;

        return $this;
    }

    /**
     * @return string
     */
    public function getMassUnit()
    {
        return $this->massUnit;
    }

    /**
     * @param string $massUnit
     * @return $this
     */
    public function setMassUnit($massUnit)
    {
        $this->massUnit = $massUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getDimensionsUnit()
    {
        return $this->dimensionsUnit;
    }

    /**
     * @param string $unit
     * @return $this
     */
    public function setDimensionsUnit($unit)
    {
        $this->dimensionsUnit = $unit;

        return $this;
    }

    /**
     * @return string
     */
    public function getSideLengthLimit()
    {
        return $this->sideLengthLimit;
    }

    /**
     * @param string $limit
     * @return $this
     */
    public function setSideLengthLimit($limit)
    {
        $this->sideLengthLimit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function getGirthLimit()
    {
        return $this->girthLimit;
    }

    /**
     * @param string $limit
     * @return $this
     */
    public function setGirthLimit($limit)
    {
        $this->girthLimit = $limit;

        return $this;
    }

    /**
     * @return UspsGirthCalculator
     */
    public function getGirthCalculator()
    {
        if (!$this->girthCalculator) {
            $this->girthCalculator = new UspsGirthCalculator($this->getMath());
        }

        return $this->girthCalculator;
    }

    /**
     * @param UspsGirthCalculator $calculator
     * @return $this
     */
    public function setGirthCalculator(UspsGirthCalculator $calculator)
    {
        $this->girthCalculator = $calculator;

        return $this;
    }
}
