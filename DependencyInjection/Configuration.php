<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\DependencyInjection;

use SmartCore\Bundle\AcceleratorCacheBundle\CacheClearerService;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('accelerator_cache');

        $rootNode
            ->children()
                ->scalarNode('host')
                    ->beforeNormalization()
                        ->always(function ($v) { return $v && strncmp($v, 'http', 4) !== 0 ? 'http://'.$v : $v; })
                    ->end()
                    ->defaultNull()
                ->end()
                ->scalarNode('web_dir')->defaultValue('%kernel.root_dir%/../web')->end()
                ->enumNode('mode')
                    ->values(array(CacheClearerService::MODE_FOPEN, CacheClearerService::MODE_CURL))
                    ->defaultValue(CacheClearerService::MODE_FOPEN)
                ->end()
                ->arrayNode('curl_opts')
                    ->validate()
                        ->always(function ($opts) {
                            foreach (array_keys($opts) as $const) {
                                if (!defined($const) || substr($const, 0, 8) !== 'CURLOPT_') {
                                    throw new \InvalidArgumentException(sprintf('%s is not a valid CURLOPT option. (http://php.net/manual/en/function.curl-setopt.php)', json_encode($const)));
                                }
                            }

                            return $opts;
                        })
                    ->end()
                    ->defaultValue(array())
                    ->useAttributeAsKey('name', false)
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
