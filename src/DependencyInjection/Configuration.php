<?php

namespace Brander\Bundle\EAVBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for BranderEAVBundle.
 *
 * @author tomfun
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string brander_eav
     */
    private $alias;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $alias
     * @param string $locale %locale%
     */
    public function __construct($alias, $locale)
    {
        $this->alias = $alias;
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        // @formatter:off
        $treeBuilder
            ->root($this->alias)
                ->children()
                    ->booleanNode('useJmsSerializer')->defaultTrue()->end()
                    ->scalarNode('statsLifeTime')->defaultValue(3600)->end()
                    ->scalarNode('fixturesDirectory')->defaultNull()->end()
                    ->scalarNode('manageRole')->defaultValue("ROLE_ADMIN")->end()
                    ->arrayNode('searchable')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                        ->arrayNode('list_class_map')
                            ->prototype('array')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) {
                                        return ['entity' => $v];
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('query')->defaultNull()->end()
                                    ->scalarNode('result')->defaultNull()->end()
                                    ->scalarNode('entity')->isRequired()->end()
                                    ->scalarNode('serviceClass')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->arrayNode('locales_supported')
                        ->defaultValue([$this->locale])
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }
}
