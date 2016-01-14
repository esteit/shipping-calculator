<?php

namespace EsteIt\ShippingCalculator\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class IParcelConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('i-parcel');

        $rootNode
            ->children()
                ->scalarNode('mass_unit')->end()
                ->scalarNode('dimensions_unit')->end()
                ->scalarNode('currency')->end()
                ->scalarNode('maximum_weight')->end()
                ->variableNode('extra_data')->end()
                ->scalarNode('maximum_dimension')->end()
                ->scalarNode('maximum_perimeter')->end()
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
                ->arrayNode('zones')
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