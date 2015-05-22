<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AcceleratorCacheExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container->setParameter('accelerator_cache.host', $mergedConfig['host'] ?: trim($mergedConfig['host'], '/'));
        $container->setParameter('accelerator_cache.web_dir', $mergedConfig['web_dir']);
        $container->setParameter('accelerator_cache.mode', $mergedConfig['mode']);

        $curlOpts = array();

        foreach ($mergedConfig['curl_opts'] as $name => $value) {
            $curlOpts[constant($name)] = $value;
        }

        $container->setParameter('accelerator_cache.curl_opts', $curlOpts);
    }
}
