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
                ->arrayNode('tariffs')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('date')->end()
                            ->scalarNode('fuel_subcharge')->end()
                            ->scalarNode('mass_unit')->end()
                            ->scalarNode('dimensions_unit')->end()
                            ->scalarNode('side_length_limit')->end()
                            ->scalarNode('girth_limit')->end()
                            ->scalarNode('currency')->end()
                            ->arrayNode('recipient_countries')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('code')->end()
                                        ->scalarNode('price_group')->end()
                                        ->scalarNode('weight_limit')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('price_groups')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->arrayNode('prices')
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
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}