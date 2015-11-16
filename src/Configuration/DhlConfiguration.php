<?php

namespace EsteIt\ShippingCalculator\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DhlConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('dhl');

        $rootNode
            ->children()
                ->scalarNode('mass_unit')->end()
                ->scalarNode('dimensions_unit')->end()
                ->scalarNode('currency')->end()
                ->scalarNode('maximum_weight')->end()
                ->variableNode('extra_data')->end()
                ->arrayNode('maximum_dimensions')
                    ->children()
                        ->scalarNode('length')->end()
                        ->scalarNode('width')->end()
                        ->scalarNode('height')->end()
                    ->end()
                ->end()
                ->scalarNode('volumetric_calculation_factor')->end()
                ->arrayNode('export_countries')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('code')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('import_countries')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('code')->end()
                            ->scalarNode('zone')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('zone_calculators')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('overweight_rate_factor')->end()
                            ->arrayNode('weight_prices')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('weight')->end()
                                        ->scalarNode('price')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}