<?php

namespace EsteIt\ShippingCalculator\CalculatorHandler;

use EsteIt\ShippingCalculator\CalculatorHandler\Asendia\ZoneCalculator;
use EsteIt\ShippingCalculator\Configuration\AsendiaConfiguration;
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
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsendiaCalculatorHandler implements CalculatorHandlerInterface
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options)
    {
        $math = new NativeMath();
        $resolver = new OptionsResolver();
        $resolver
            ->setDefined([
                'extra_data'
            ])
            ->setDefaults([
                'currency' => 'USD',
                'math' => $math,
                'weight_converter' => new WeightConverter($math),
                'length_converter' => new LengthConverter($math),
                'girth_calculator' => new UspsGirthCalculator($math),
                'extra_data' => null,
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


        $resolver->setNormalizer('import_countries', $this->createImportCountriesNormalizer());
        $resolver->setNormalizer('export_countries', $this->createExportCountriesNormalizer());
        $resolver->setNormalizer('zone_calculators', $this->createZoneCalculatorsNormalizer());

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
        $weight = $this->getWeightConverter()->convert($weight->getValue(), $weight->getUnit(), $this->get('mass_unit'));

        $math = $this->getMath();
        $cost = $zoneCalculator->calculate($weight);
        $wholeWeight = $math->roundUp($weight);
        $fuelCost = $math->mul($wholeWeight, $this->get('fuel_subcharge'));
        $total = $math->sum($cost, $fuelCost);

        $result->setTotalCost($total);
        $result->setCurrency($this->get('currency'));
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

        if (!array_key_exists($importCountry->getZone(), $this->get('zone_calculators'))) {
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
        $maximumDimension = $converter->convert($this->get('maximum_dimension'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($dimensions->getLength(), $maximumDimension)) {
            throw new InvalidDimensionsException('Side length limit is exceeded.');
        }

        $girth = $girthCalculator->calculate($dimensions);
        $maxGirth = $converter->convert($this->get('maximum_girth'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($girth->getValue(), $maxGirth)) {
            throw new InvalidDimensionsException('Girth limit is exceeded.');
        }
    }

    public function validateWeight(PackageInterface $package)
    {
        $math = $this->getMath();
        $converter = $this->getWeightConverter();
        $country = $this->getImportCountry($package->getRecipientAddress()->getCountryCode());

        $countryMaxWeight = $converter->convert($country->getMaximumWeight(), $this->get('mass_unit'), $package->getWeight()->getUnit());
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

        $calculators = $this->get('zone_calculators');
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
        $countries = $this->get('export_countries');
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
        $countries = $this->get('import_countries');
        if (!array_key_exists($code, $countries)) {
            throw new InvalidArgumentException();
        }

        return $countries[$code];
    }

    public static function create(array $config)
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new AsendiaConfiguration(), [$config]);

        return new static($processedConfig);
    }

    /**
     * @return MathInterface
     */
    protected function getMath()
    {
        return $this->get('math');
    }

    /**
     * @return UnitConverterInterface
     */
    protected function getLengthConverter()
    {
        return $this->get('length_converter');
    }

    /**
     * @return UnitConverterInterface
     */
    protected function getWeightConverter()
    {
        return $this->get('weight_converter');
    }

    /**
     * @return UspsGirthCalculator
     */
    protected function getGirthCalculator()
    {
        return $this->get('girth_calculator');
    }

    /**
     * @return \Closure
     */
    protected function createImportCountriesNormalizer()
    {
        return function (Options $options, $value) {
            $normalized = [];
            foreach ($value as $country) {
                if (!$country instanceof ImportCountry) {
                    $config = $country;
                    $country = new ImportCountry();
                    $country->setCode($config['code']);
                    $country->setZone($config['zone']);
                    $country->setMaximumWeight($config['maximum_weight']);
                }
                $normalized[$country->getCode()] = $country;
            }
            return $normalized;
        };
    }

    /**
     * @return \Closure
     */
    protected function createExportCountriesNormalizer()
    {
        return function (Options $options, $value) {
            $normalized = [];
            foreach ($value as $country) {
                if (!$country instanceof ExportCountry) {
                    $config = $country;
                    $country = new ExportCountry();
                    $country->setCode($config['code']);
                }
                $normalized[$country->getCode()] = $country;
            }
            return $normalized;
        };
    }

    /**
     * @return \Closure
     */
    protected function createZoneCalculatorsNormalizer()
    {
        return function (Options $options, $calculators) {
            $normalized = [];
            foreach ($calculators as $calculator) {
                if (!$calculator instanceof ZoneCalculator) {
                    $calculator = new ZoneCalculator($calculator);
                }
                $normalized[$calculator->getName()] = $calculator;
            }
            return $normalized;
        };
    }

    /**
     * @param mixed $name
     * @return mixed null
     */
    public function get($name)
    {
        return $this->options && array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }
}
