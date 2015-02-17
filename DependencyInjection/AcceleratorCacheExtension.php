<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AcceleratorCacheExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['host'] && strncmp($config['host'], 'http', 4) !== 0) {
            $config['host'] = 'http://'.$config['host'];
        }
        $container->setParameter('accelerator_cache.host', $config['host'] ? trim($config['host'], '/') : false);
        $container->setParameter('accelerator_cache.web_dir', $config['web_dir']);
        $container->setParameter('accelerator_cache.mode', $config['mode']);

        $curlOpts = array();
        foreach ($config['curl_opts'] as $name => $value) {
            $curlOpts[constant($name)] = $value;
        }
        $container->setParameter('accelerator_cache.curl_opts', $curlOpts);
    }
}
