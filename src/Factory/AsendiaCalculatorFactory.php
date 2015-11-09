<?php

namespace EsteIt\ShippingCalculator\Factory;

use EsteIt\ShippingCalculator\Configuration\AsendiaConfiguration;
use EsteIt\ShippingCalculator\Calculator\Asendia\PriceGroup;
use EsteIt\ShippingCalculator\Calculator\Asendia\RecipientCountry;
use EsteIt\ShippingCalculator\Calculator\Asendia\Tariff;
use EsteIt\ShippingCalculator\Calculator\AsendiaCalculator;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class AsendiaCalculatorFactory
 */
class AsendiaCalculatorFactory
{
    public function create(array $config)
    {
        $config = $this->processConfig($config);

        $tariffs = $this->createTariffs($config['tariffs']);

        $calculator = new AsendiaCalculator();
        $calculator->addTariffs($tariffs);

        return $calculator;
    }

    protected function createTariffs(array $tariffsConfig)
    {
        $tariffs = [];

        foreach ($tariffsConfig as $tariffConfig) {
            $recipientCountries = $this->createRecipientCountries($tariffConfig['recipient_countries']);
            $priceGroups = $this->createPriceGroups($tariffConfig['price_groups']);

            $tariff = new Tariff();

            $tariff->setDate(new \DateTime($tariffConfig['date']));
            $tariff->addRecipientCountries($recipientCountries);
            $tariff->addPriceGroups($priceGroups);
            $tariff->setFuelSubcharge($tariffConfig['fuel_subcharge']);
            $tariff->setWeightUnit($tariffConfig['weight_unit']);

            $tariffs[] = $tariff;
        }

        return $tariffs;
    }

    protected function createRecipientCountries(array $recipientCountriesConfig)
    {
        $recipientCountries = [];
        foreach ($recipientCountriesConfig as $countryConfig) {
            $recipientCountry = new RecipientCountry();
            $recipientCountry->setCode($countryConfig['code']);
            $recipientCountry->setPriceGroup($countryConfig['price_group']);
            $recipientCountry->setWeightLimit($countryConfig['weight_limit']);

            $recipientCountries[] = $recipientCountry;
        }

        return $recipientCountries;
    }

    protected function createPriceGroups(array $priceGroupsConfig)
    {
        $priceGroups = [];

        foreach ($priceGroupsConfig as $priceGroupConfig) {
            $priceGroup = new PriceGroup();
            $priceGroup->setName($priceGroupConfig['name']);
            foreach ($priceGroupConfig['prices'] as $priceConfig) {
                $priceGroup->setPrice($priceConfig['weight'], $priceConfig['price']);
            }
            $priceGroups[] = $priceGroup;
        }

        return $priceGroups;
    }

    protected function processConfig(array $config)
    {
        $processor = new Processor();
        $configuration = new AsendiaConfiguration();
        $processedConfig = $processor->processConfiguration(
            $configuration,
            [$config]
        );

        return $processedConfig;
    }
}
