<?php

namespace EsteIt\ShippingCalculator\CalculatorHandler;

use EsteIt\ShippingCalculator\Configuration\AramexConfiguration;
use EsteIt\ShippingCalculator\Exception\InvalidDimensionsException;
use EsteIt\ShippingCalculator\Exception\InvalidRecipientAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidSenderAddressException;
use EsteIt\ShippingCalculator\Exception\InvalidWeightException;
use EsteIt\ShippingCalculator\Model\AddressInterface;
use EsteIt\ShippingCalculator\Tool\DimensionsNormalizer;
use EsteIt\ShippingCalculator\Tool\MaximumPerimeterCalculator;
use EsteIt\ShippingCalculator\Model\CalculationResultInterface;
use EsteIt\ShippingCalculator\Model\ExportCountry;
use EsteIt\ShippingCalculator\Model\ImportCountry;
use EsteIt\ShippingCalculator\Model\PackageInterface;
use EsteIt\ShippingCalculator\VolumetricWeightCalculator\AramexVolumetricWeightCalculator;
use Moriony\Trivial\Converter\LengthConverter;
use Moriony\Trivial\Converter\UnitConverterInterface;
use Moriony\Trivial\Converter\WeightConverter;
use Moriony\Trivial\Math\MathInterface;
use Moriony\Trivial\Math\NativeMath;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AramexCalculatorHandler implements CalculatorHandlerInterface
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options)
    {
        $math = new NativeMath();
        $resolver = new OptionsResolver();
        $weightConverter = new WeightConverter($math);
        $lengthConverter = new LengthConverter($math);
        $dimensionsNormalizer = new DimensionsNormalizer($math);

        $resolver
            ->setDefined([
                'extra_data'
            ])
            ->setDefaults([
                'currency' => 'USD',
                'math' => $math,
                'weight_converter' => $weightConverter,
                'length_converter' => $lengthConverter,
                'perimeter_calculator' => new MaximumPerimeterCalculator($math, $dimensionsNormalizer),
                'volumetric_weight_calculator' => new AramexVolumetricWeightCalculator($math, $weightConverter, $lengthConverter),
                'dimensions_normalizer' => $dimensionsNormalizer,
                'extra_data' => null,
            ])
            ->setRequired([
                'export_countries',
                'import_countries',
                'zones',
                'currency',
                'math',
                'weight_converter',
                'length_converter',
                'mass_unit',
                'dimensions_unit',
                'maximum_perimeter',
                'maximum_dimension',
                'maximum_weight',
            ])
            ->setAllowedTypes([
                'export_countries' => 'array',
                'import_countries' => 'array',
                'zones' => 'array',
                'currency' => 'string',
                'math' => 'Moriony\Trivial\Math\MathInterface',
                'weight_converter' => 'Moriony\Trivial\Converter\UnitConverterInterface',
                'length_converter' => 'Moriony\Trivial\Converter\UnitConverterInterface',
                'perimeter_calculator' => 'EsteIt\ShippingCalculator\Tool\MaximumPerimeterCalculator',
                'volumetric_weight_calculator' => 'EsteIt\ShippingCalculator\VolumetricWeightCalculator\AramexVolumetricWeightCalculator',
                'dimensions_normalizer' => 'EsteIt\ShippingCalculator\Tool\DimensionsNormalizer',
            ]);


        $resolver->setNormalizer('import_countries', $this->createImportCountriesNormalizer());
        $resolver->setNormalizer('export_countries', $this->createExportCountriesNormalizer());
        $resolver->setNormalizer('zones', $this->createZonesNormalizer());

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
        $this->validateMaximumDimension($package);
        $this->validateMaximumPerimeter($package);
        $this->validateWeight($package);
        $price = $this->getPrice($package);

        $result->setShippingCost($price);
        $result->setCurrency($this->get('currency'));
    }

    public function validateSenderAddress(AddressInterface $address)
    {
        $countries = $this->get('export_countries');
        if (!array_key_exists($address->getCountryCode(), $countries)) {
            throw new InvalidSenderAddressException('Can not send a package from this country.');
        }
    }

    public function validateRecipientAddress(AddressInterface $address)
    {
        $importCountry = $this->detectImportCountry($address);
        if (is_null($importCountry)) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }

        if (!array_key_exists($importCountry->getZone(), $this->get('zones'))) {
            throw new InvalidRecipientAddressException('Can not send a package to this country.');
        }
    }

    public function validateMaximumDimension(PackageInterface $package)
    {
        $dimensions = $this->getDimensionsNormalizer()->normalize($package->getDimensions());
        $maximumDimension = $this->getLengthConverter()->convert($this->get('maximum_dimension'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($this->getMath()->greaterThan($dimensions->getLength(), $maximumDimension)) {
            throw new InvalidDimensionsException('Side length limit is exceeded.');
        }
    }

    public function validateMaximumPerimeter(PackageInterface $package)
    {
        $dimensions = $package->getDimensions();
        $perimeter = $this->getPerimeterCalculator()->calculate($dimensions);
        $maxPerimeter = $this->getLengthConverter()->convert($this->get('maximum_perimeter'), $this->get('dimensions_unit'), $dimensions->getUnit());
        if ($this->getMath()->greaterThan($perimeter->getValue(), $maxPerimeter)) {
            throw new InvalidDimensionsException('Maximum perimeter limit is exceeded.');
        }
    }

    public function validateWeight(PackageInterface $package)
    {
        $importCountry = $this->detectImportCountry($package->getRecipientAddress());

        $weight = $package->getWeight();
        $math = $this->getMath();
        if ($math->lessThan($weight->getValue(), 0)) {
            throw new InvalidWeightException('Weight should be greater than zero.');
        }

        $countryMaxWeight = $this->getWeightConverter()->convert($importCountry->getMaximumWeight(), $this->get('mass_unit'), $weight->getUnit());
        if ($this->getMath()->greaterThan($weight->getValue(), $countryMaxWeight)) {
            throw new InvalidWeightException('Sender country weight limit is exceeded.');
        }
    }

    public function getPrice(PackageInterface $package)
    {
        $weight = $package->getWeight();
        $volumetricWeight = $this->getVolumetricWeightCalculator()->calculate($package->getDimensions());

        $weight = $this->getWeightConverter()->convert($weight->getValue(), $weight->getUnit(), $this->get('mass_unit'));
        $volumetricWeight = $this->getWeightConverter()->convert($volumetricWeight->getValue(), $volumetricWeight->getUnit(), $this->get('mass_unit'));

        $math = $this->getMath();
        if ($math->greaterThan($volumetricWeight, $weight)) {
            $weight = $volumetricWeight;
        }

        $importCountry = $this->detectImportCountry($package->getRecipientAddress());
        $zone = $this->get('zones')[$importCountry->getZone()];

        $currentWeight = null;
        $price = null;

        foreach ($zone['weight_prices'] as $w => $p) {
            if ($math->lessOrEqualThan($weight, $w) && $math->greaterThan($weight, $currentWeight)) {
                $currentWeight = $w;
                $price = $p;
            }
        }

        if (is_null($price)) {
            throw new InvalidWeightException('Can not calculate shipping for this weight.');
        }

        return $price;
    }

    public static function create(array $config)
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new AramexConfiguration(), [$config]);

        return new static($processedConfig);
    }

    /**
     * @param AddressInterface $address
     * @return ImportCountry|null
     */
    protected function detectImportCountry(AddressInterface $address)
    {
        $countries = $this->get('import_countries');
        $importCountry = null;
        if (array_key_exists($address->getCountryCode(), $countries)) {
            $importCountry = $countries[$address->getCountryCode()];
        }
        return $importCountry;
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
     * @return MaximumPerimeterCalculator
     */
    protected function getPerimeterCalculator()
    {
        return $this->get('perimeter_calculator');
    }

    /**
     * @return AramexVolumetricWeightCalculator
     */
    protected function getVolumetricWeightCalculator()
    {
        return $this->get('volumetric_weight_calculator');
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
                    $country->setMaximumWeight($options['maximum_weight']);
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
    protected function createZonesNormalizer()
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'math' => new NativeMath(),
            ])
            ->setRequired([
                'math',
                'name',
                'weight_prices',
            ])
            ->setAllowedTypes([
                'math' => 'Moriony\Trivial\Math\MathInterface',
                'weight_prices' => 'array',
            ])
            ->setNormalizer('weight_prices', $this->createWeightPricesNormalizer());

        return function (Options $options, $zones) use($resolver) {
            $normalized = [];
            foreach ($zones as $zone) {
                $zone = $resolver->resolve($zone);
                $normalized[$zone['name']] = $zone;
            }
            return $normalized;
        };
    }

    protected function createWeightPricesNormalizer()
    {
        return function (Options $options, $weightPrices) {
            $normalized = [];
            foreach ($weightPrices as $weightPrice) {
                $normalized[$weightPrice['weight']] = $weightPrice['price'];
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
