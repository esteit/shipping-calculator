<?php

namespace EsteIt\ShippingCalculator\Calculator;

use EsteIt\ShippingCalculator\Calculator\Asendia\ZoneCalculator;
use EsteIt\ShippingCalculator\Exception\InvalidConfigurationException;
use EsteIt\ShippingCalculator\Exception\InvalidDimensionsException;
use EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Exception\InvalidWeightException;
use EsteIt\ShippingCalculator\GirthCalculator\UspsGirthCalculator;
use EsteIt\ShippingCalculator\Model\AddressInterface;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\ExportCountry;
use EsteIt\ShippingCalculator\Model\ImportCountry;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\UnitConverterInterface;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\NativeMath;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsendiaCalculator extends AbstractCalculator
{
    public function __construct(array $options)
    {
        $math = new NativeMath();
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'currency' => 'USD',
                'math' => $math,
                'weight_converter' => new WeightConverter($math),
                'length_converter' => new LengthConverter($math),
                'girth_calculator' => new UspsGirthCalculator($math),
            ])
            ->setRequired([
                'export_countries',
                'import_countries',
                'zone_calculators',
                'currency',
                'fuel_subcharge',
                'math',
                'weight_converter',
                'length_converter',
                'mass_unit',
                'dimensions_unit',
                'maximum_girth',
                'maximum_dimension'
            ])
            ->setAllowedTypes([
                'export_countries' => 'array',
                'import_countries' => 'array',
                'zone_calculators' => 'array',
                'currency' => 'string',
                'math' => 'Moriony\Trivial\Math\MathInterface',
                'weight_converter' => 'Moriony\Trivial\Converter\UnitConverterInterface',
                'length_converter' => 'Moriony\Trivial\Converter\UnitConverterInterface',
                'girth_calculator' => 'EsteIt\ShippingCalculator\GirthCalculator\UspsGirthCalculator',
            ]);

        $countriesNormalizer = function (Options $options, $countries) {
            $normalized = [];
            /** @var ImportCountry|ExportCountry $country */
            foreach ($countries as $country) {
                $normalized[$country->getCode()] = $country;
            }
            return $normalized;
        };

        $zoneCalculatorsNormalizer = function (Options $options, $calculators) {
            $normalized = [];
            /** @var ZoneCalculator $calculator */
            foreach ($calculators as $calculator) {
                $normalized[$calculator->getName()] = $calculator;
            }
            return $normalized;
        };

        $resolver->setNormalizer('import_countries', $countriesNormalizer);
        $resolver->setNormalizer('export_countries', $countriesNormalizer);
        $resolver->setNormalizer('zone_calculators', $zoneCalculatorsNormalizer);

        $this->options = $resolver->resolve($options);
    }

    /**
     * @param CalculationResultInterface $result
     * @param PackageInterface $package
     * @return mixed
     */
    public function visit(CalculationResultInterface $result, PackageInterface $package)
    {
        $this->validateSenderAddress($package->getSenderAddress());
        $this->validateRecipientAddress($package->getRecipientAddress());
        $this->validateDimensions($package);
        $this->validateWeight($package);

        $zoneCalculator = $this->getZoneCalculator($package);

        $weight = $package->getWeight();
        $weight = $this->getWeightConverter()->convert($weight->getValue(), $weight->getUnit(), $this->getOption('mass_unit'));

        $math = $this->getMath();
        $cost = $zoneCalculator->calculate($weight);
        $wholeWeight = $math->roundUp($weight);
        $fuelCost = $math->mul($wholeWeight, $this->getOption('fuel_subcharge'));
        $total = $math->sum($cost, $fuelCost);
        $total = $math->roundUp($total, 2);
        $total = number_format($total, 2, '.', '');

        $result->setTotalCost($total);
        $result->setCurrency($this->getOption('currency'));
    }

    /**
     * @param AddressInterface $address
     */
    public function validateSenderAddress(AddressInterface $address)
    {
        try {
            $this->getExportCountry($address->getCountryCode());
        } catch (InvalidArgumentException $e) {
            throw new InvalidSenderAddressException('Can not send a package from this country.');
        }
    }

    /**
     * @param AddressInterface $address
     */
    public function validateRecipientAddress(AddressInterface $address)
    {
        try {
            $importCountry = $this->getImportCountry($address->getCountryCode());
        } catch (InvalidArgumentException $e) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }

        if (!array_key_exists($importCountry->getZone(), $this->getOption('zone_calculators'))) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }
    }

    /**
     * @param PackageInterface $package
     */
    public function validateDimensions(PackageInterface $package)
    {
        $math = $this->getMath();
        $converter = $this->getLengthConverter();
        $girthCalculator = $this->getGirthCalculator();

        $dimensions = $girthCalculator->normalizeDimensions($package->getDimensions());
        $maximumDimension = $converter->convert($this->getOption('maximum_dimension'), $this->getOption('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($dimensions->getLength(), $maximumDimension)) {
            throw new InvalidDimensionsException('Side length limit is exceeded.');
        }

        $girth = $girthCalculator->calculate($dimensions);
        $maxGirth = $converter->convert($this->getOption('maximum_girth'), $this->getOption('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($girth->getValue(), $maxGirth)) {
            throw new InvalidDimensionsException('Girth limit is exceeded.');
        }
    }

    public function validateWeight(PackageInterface $package)
    {
        $math = $this->getMath();
        $converter = $this->getWeightConverter();
        $country = $this->getImportCountry($package->getRecipientAddress()->getCountryCode());

        $countryMaxWeight = $converter->convert($country->getMaximumWeight(), $this->getOption('mass_unit'), $package->getWeight()->getUnit());
        if ($math->greaterThan($package->getWeight()->getValue(), $countryMaxWeight)) {
            throw new InvalidWeightException('Sender country weight limit is exceeded.');
        }
    }

    /**
     * @param PackageInterface $package
     * @return ZoneCalculator
     */
    public function getZoneCalculator($package)
    {
        $country = $this->getImportCountry($package->getRecipientAddress()->getCountryCode());

        $calculators = $this->getOption('zone_calculators');
        if (!array_key_exists($country->getZone(), $calculators)) {
            throw new InvalidConfigurationException('Price group does not exist.');
        }

        return $calculators[$country->getZone()];
    }

    /**
     * @param string $code
     * @return ExportCountry
     */
    public function getExportCountry($code)
    {
        $countries = $this->getOption('export_countries');
        if (!array_key_exists($code, $countries)) {
            throw new InvalidArgumentException();
        }

        return $countries[$code];
    }

    /**
     * @param string $code
     * @return ImportCountry
     */
    public function getImportCountry($code)
    {
        $countries = $this->getOption('import_countries');
        if (!array_key_exists($code, $countries)) {
            throw new InvalidArgumentException();
        }

        return $countries[$code];
    }

    /**
     * @return MathInterface
     */
    protected function getMath()
    {
        return $this->getOption('math');
    }

    /**
     * @return UnitConverterInterface
     */
    protected function getLengthConverter()
    {
        return $this->getOption('length_converter');
    }

    /**
     * @return UnitConverterInterface
     */
    protected function getWeightConverter()
    {
        return $this->getOption('weight_converter');
    }

    /**
     * @return UspsGirthCalculator
     */
    protected function getGirthCalculator()
    {
        return $this->getOption('girth_calculator');
    }
}
