<?php

namespace SmartCore\Bundle\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('smart_media');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('default_storage')->defaultNull()->end()
                ->scalarNode('default_filter')->defaultNull()->end()
                ->scalarNode('upload_filter')->defaultNull()->end()
                ->scalarNode('file_relative_path_pattern')->defaultValue('/{year}/{month}/{day}')->end()
                ->scalarNode('filename_pattern')->defaultValue('{hour}_{minutes}_{rand(10)}')->end()
                ->arrayNode('storages')
                    ->canBeUnset()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('code')->end()
                            ->scalarNode('provider')->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->scalarNode('relative_path')->defaultValue('')->end()
                            ->arrayNode('arguments')
                                ->canBeUnset()
                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('collections')
                    ->canBeUnset()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('code')->end()
                            ->scalarNode('title')->end()
                            ->scalarNode('relative_path')->defaultValue('')->end()
                            ->scalarNode('storage')->defaultNull()->end()
                            ->scalarNode('default_filter')->defaultNull()->end()
                            ->scalarNode('upload_filter')->defaultNull()->end()
                            ->scalarNode('file_relative_path_pattern')->defaultNull()->end()
                            ->scalarNode('filename_pattern')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
