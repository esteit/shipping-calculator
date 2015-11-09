<?php

namespace EsteIt\ShippingCalculator\Calculator\Asendia;

use EsteIt\ShippingCalculator\Exception\InvalidConfigurationException;
use EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException;
use EsteIt\ShippingCalculator\Model\PackageInterface;
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
     * @var string
     */
    protected $massUnit;
    /**
     * @var string
     */
    protected $currency;

    /**
     * Tariff constructor.
     */
    public function __construct()
    {
        $this->senderCountries = ['USA'];
        $this->recipientCountries = [];
        $this->currency = 'USD';
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
     * @return mixed
     */
    public function calculate(PackageInterface $package)
    {
        $isValidSenderCountry = in_array($package->getSenderAddress()->getCountryCode(), $this->senderCountries);
        if (!$isValidSenderCountry) {
            throw new InvalidSenderAddressException();
        }

        $country = $this->getRecipientCountry($package->getRecipientAddress()->getCountryCode());
        $priceGroup = $this->getPriceGroup($country->getPriceGroup());

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
     * @param string $number
     * @return PriceGroup
     */
    public function getPriceGroup($number)
    {
        if (!array_key_exists($number, $this->priceGroups)) {
            throw new InvalidConfigurationException('Price group does not exist.');
        }

        return $this->priceGroups[$number];
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
            throw new InvalidRecipientAddressException();
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
     * @param UnitConverterInterface $weightConverter
     * @return $this
     */
    public function setWeightConverter(UnitConverterInterface $weightConverter)
    {
        $this->weightConverter = $weightConverter;

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
}
