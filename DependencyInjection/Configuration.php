<?php

namespace tbn\TempoTelemetryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tempo_telemetry');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
        ->children()
            ->integerNode('timeout')->defaultValue(3)->end()
            ->scalarNode('service_name')->isRequired()->end()
            ->scalarNode('tempo_url')->isRequired()->end()
        ->end();

        return $treeBuilder;
    }
}
