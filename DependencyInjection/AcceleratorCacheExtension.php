<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class AcceleratorCacheExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $resources = __DIR__.'/../Resources';

        $loader = new Loader\XmlFileLoader($container, new FileLocator($resources));
        $loader->load('services.xml');

        if (null === $host = $mergedConfig['host']) {
            $host = '%router.request_context.scheme%://%router.request_context.host%';
        }

        $container->setParameter('accelerator_cache.host', trim($host, '/'));
        $container->setParameter('accelerator_cache.web_dir', $mergedConfig['web_dir']);
        $container->setParameter('accelerator_cache.mode', $mergedConfig['mode']);
        $container->setParameter('accelerator_cache.template', file_get_contents($resources.'/template.tpl'));

        $curlOpts = array();

        foreach ($mergedConfig['curl_opts'] as $name => $value) {
            $curlOpts[constant($name)] = $value;
        }

        $container->setParameter('accelerator_cache.curl_opts', $curlOpts);
    }
}
