<?php

namespace EsteIt\ShippingCalculator\Handler;

use EsteIt\ShippingCalculator\Address;
use EsteIt\ShippingCalculator\Exception\ViolationException;
use EsteIt\ShippingCalculator\Handler\Asendia\ZoneCalculator;
use EsteIt\ShippingCalculator\Configuration\AsendiaConfiguration;
use EsteIt\ShippingCalculator\Exception\InvalidConfigurationException;
use EsteIt\ShippingCalculator\Exception\InvalidArgumentException;
use EsteIt\ShippingCalculator\Package;
use EsteIt\ShippingCalculator\Result;
use EsteIt\ShippingCalculator\Tool\DimensionsNormalizer;
use EsteIt\ShippingCalculator\Tool\UspsGirthCalculator;
use EsteIt\ShippingCalculator\Model\ExportCountry;
use EsteIt\ShippingCalculator\Model\ImportCountry;
use EsteIt\ShippingCalculator\Violation;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\UnitConverterInterface;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\NativeMath;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsendiaHandler implements HandlerInterface, ValidationHandlerInterface
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options)
    {
        $math = new NativeMath();
        $resolver = new OptionsResolver();
        $dimensionsNormalizer = new DimensionsNormalizer($math);
        $resolver
            ->setDefined([
                'extra_data'
            ])
            ->setDefaults([
                'currency' => 'USD',
                'math' => $math,
                'weight_converter' => new WeightConverter($math),
                'length_converter' => new LengthConverter($math),
                'girth_calculator' => new UspsGirthCalculator($math, $dimensionsNormalizer),
                'dimensions_normalizer' => $dimensionsNormalizer,
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
                'math' => MathInterface::class,
                'weight_converter' => UnitConverterInterface::class,
                'length_converter' => UnitConverterInterface::class,
                'girth_calculator' => UspsGirthCalculator::class,
                'dimensions_normalizer' => DimensionsNormalizer::class,
            ]);


        $resolver->setNormalizer('import_countries', $this->createImportCountriesNormalizer());
        $resolver->setNormalizer('export_countries', $this->createExportCountriesNormalizer());
        $resolver->setNormalizer('zone_calculators', $this->createZoneCalculatorsNormalizer());

        $this->options = $resolver->resolve($options);
    }

    /**
     * @param Result $result
     * @param Package $package
     */
    public function validate(Result $result, Package $package)
    {
        try {
            $this->validateSenderAddress($package->getSenderAddress());
        } catch (ViolationException $e) {
            $result->addViolation(new Violation($e->getMessage()));
        }

        try {
            $this->validateRecipientAddress($package->getRecipientAddress());
        } catch (ViolationException $e) {
            $result->addViolation(new Violation($e->getMessage()));
        }

        try {
            $this->validateDimensions($package);
        } catch (ViolationException $e) {
            $result->addViolation(new Violation($e->getMessage()));
        }

        try {
            $this->validateWeight($package);
        } catch (ViolationException $e) {
            $result->addViolation(new Violation($e->getMessage()));
        }
    }

    /**
     * @param Result $result
     * @param Package $package
     * @return mixed
     */
    public function calculate(Result $result, Package $package)
    {
        $this->validate($result, $package);

        if (!$result->hasViolations()) {
            try {
                $zoneCalculator = $this->getZoneCalculator($package);

                $weight = $package->getWeight();
                $weight = $this->getWeightConverter()->convert($weight->getValue(), $weight->getUnit(), $this->get('mass_unit'));

                $cost = $zoneCalculator->calculate($weight);
                $wholeWeight = $this->getMath()->roundUp($weight);
                $fuelCost = $this->getMath()->mul($wholeWeight, $this->get('fuel_subcharge'));
                $total = $this->getMath()->sum($cost, $fuelCost);

                $result->set('shipping_cost', $total);
            } catch (ViolationException $e) {
                $result->addViolation(new Violation($e->getMessage()));
            }
        }
    }

    /**
     * @param Address $address
     */
    public function validateSenderAddress(Address $address)
    {
        try {
            $this->getExportCountry($address->getCountryCode());
        } catch (InvalidArgumentException $e) {
            throw new ViolationException('Can not send a package from this country.');
        }
    }

    /**
     * @param Address $address
     */
    public function validateRecipientAddress(Address $address)
    {
        try {
            $importCountry = $this->getImportCountry($address->getCountryCode());
        } catch (InvalidArgumentException $e) {
            throw new ViolationException('Can not send a package to this country.');
        }

        if (!array_key_exists($importCountry->getZone(), $this->get('zone_calculators'))) {
            throw new ViolationException('Can not send a package to this country.');
        }
    }

    /**
     * @param Package $package
     */
    public function validateDimensions(Package $package)
    {
        $math = $this->getMath();
        $converter = $this->getLengthConverter();
        $girthCalculator = $this->getGirthCalculator();

        $dimensions = $this->getDimensionsNormalizer()->normalize($package->getDimensions());
        $maximumDimension = $converter->convert($this->get('maximum_dimension'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($dimensions->getLength(), $maximumDimension)) {
            throw new ViolationException('Side length limit is exceeded.');
        }

        $girth = $girthCalculator->calculate($dimensions);
        $maxGirth = $converter->convert($this->get('maximum_girth'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($math->greaterThan($girth->getValue(), $maxGirth)) {
            throw new ViolationException('Girth limit is exceeded.');
        }
    }

    public function validateWeight(Package $package)
    {
        $math = $this->getMath();
        $converter = $this->getWeightConverter();
        $country = $this->getImportCountry($package->getRecipientAddress()->getCountryCode());

        $countryMaxWeight = $converter->convert($country->getMaximumWeight(), $this->get('mass_unit'), $package->getWeight()->getUnit());
        if ($math->greaterThan($package->getWeight()->getValue(), $countryMaxWeight)) {
            throw new ViolationException('Sender country weight limit is exceeded.');
        }
    }

    /**
     * @param Package $package
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
     * @return DimensionsNormalizer
     */
    protected function getDimensionsNormalizer()
    {
        return $this->get('dimensions_normalizer');
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
