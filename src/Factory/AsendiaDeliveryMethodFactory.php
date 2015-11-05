<?php

namespace EsteIt\PackageDeliveryCalculator\Factory;

use EsteIt\PackageDeliveryCalculator\Configuration\AsendiaConfiguration;
use EsteIt\PackageDeliveryCalculator\DeliveryMethod\Asendia\PriceGroup;
use EsteIt\PackageDeliveryCalculator\DeliveryMethod\Asendia\RecipientCountry;
use EsteIt\PackageDeliveryCalculator\DeliveryMethod\Asendia\Tariff;
use EsteIt\PackageDeliveryCalculator\DeliveryMethod\AsendiaDeliveryMethod;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class AsendiaDeliveryMethodFactory
 */
class AsendiaDeliveryMethodFactory
{
    public function create(array $config)
    {
        $config = $this->processConfig($config);

        $tariffs = $this->createTariffs($config['tariffs']);

        $deliveryMethod = new AsendiaDeliveryMethod();
        $deliveryMethod->addTariffs($tariffs);

        return $deliveryMethod;
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
