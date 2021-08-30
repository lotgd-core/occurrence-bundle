<?php

/**
 * This file is part of "LoTGD Core Package - Occurrence".
 *
 * @see https://github.com/lotgd-core/occurrence-bundle
 *
 * @license https://github.com/lotgd-core/occurrence-bundle/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 0.1.0
 */

namespace Lotgd\CoreBundle\OccurrenceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('lotgd_occurrence');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->beforeNormalization()
                ->ifArray()
                ->then(function ($v) {
                    return array_merge([
                        'forest' => [
                            'probability' =>  1500
                        ],
                        'village' => [
                            'probability' =>  500
                        ],
                        'inn' => [
                            'probability' =>  500
                        ],
                        'graveyard' => [
                            'probability' =>  1000
                        ],
                        'garden' => [
                            'probability' =>  500
                        ]
                    ], $v);
                })
            ->end()
            ->arrayPrototype()
                ->children()
                    ->integerNode('probability')
                        ->isRequired()
                        ->min(0)
                        ->max(10000)
                        ->info('Probability of activate this event zone. Int 0-10000 (10000 is equal to 100.00%)')
                    ->end()
                    ->integerNode('max_activation')
                        ->min(1)
                        ->max(10)
                        ->defaultValue(2)
                        ->info('Optional: Maximun number of events that can be activated in this zone.')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
