<?php

namespace EsteIt\ShippingCalculator\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class AsendiaConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('asendia');

        $rootNode
            ->children()
                ->scalarNode('date')->end()
                ->scalarNode('fuel_subcharge')->end()
                ->scalarNode('mass_unit')->end()
                ->scalarNode('dimensions_unit')->end()
                ->scalarNode('maximum_dimension')->end()
                ->scalarNode('maximum_girth')->end()
                ->scalarNode('currency')->end()
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
                            ->scalarNode('maximum_weight')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('zone_calculators')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
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