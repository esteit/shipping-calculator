<?php

namespace EsteIt\ShippingCalculator\Factory;

use EsteIt\ShippingCalculator\Calculator\Asendia\ZoneCalculator;
use EsteIt\ShippingCalculator\Configuration\AsendiaConfiguration;
use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;
use EsteIt\ShippingCalculator\Model\ExportCountry;
use EsteIt\ShippingCalculator\Model\ImportCountry;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AsendiaCalculatorFactory
{
    public function create(array $config)
    {
        $config = $this->processConfig($config);
        $config = $this->normalizeConfig($config);
        return new AsendiaCalculator($config);
    }

    protected function processConfig(array $config)
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new AsendiaConfiguration(), [$config]);

        return $processedConfig;
    }

    protected function normalizeConfig(array $config)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'currency',
            'dimensions_unit',
            'fuel_subcharge',
            'mass_unit',
            'maximum_dimension',
            'maximum_girth',
            'export_countries',
            'import_countries',
            'zone_calculators',
        ]);

        $exportCountriesNormalizer = function (Options $options, $value) {
            $normalized = [];
            foreach ($value as $config) {
                $country = new ExportCountry();
                $country->setCode($config['code']);
                $normalized[] = $country;
            }
            return $normalized;
        };

        $importCountriesNormalizer = function (Options $options, $value) {
            $normalized = [];
            foreach ($value as $config) {
                $country = new ImportCountry();
                $country->setCode($config['code']);
                $country->setZone($config['zone']);
                $country->setMaximumWeight($config['maximum_weight']);
                $normalized[] = $country;
            }
            return $normalized;
        };

        $zoneCalculatorsNormalizer = function (Options $options, $value) {
            $normalized = [];
            foreach ($value as $config) {
                $normalized[] = new ZoneCalculator($config);
            }
            return $normalized;
        };

        $resolver->setNormalizer('export_countries', $exportCountriesNormalizer);
        $resolver->setNormalizer('import_countries', $importCountriesNormalizer);
        $resolver->setNormalizer('zone_calculators', $zoneCalculatorsNormalizer);

        return $resolver->resolve($config);
    }
}
