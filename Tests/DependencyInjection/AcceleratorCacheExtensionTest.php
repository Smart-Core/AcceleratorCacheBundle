<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use SmartCore\Bundle\AcceleratorCacheBundle\DependencyInjection\AcceleratorCacheExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AcceleratorCacheExtensionTest extends AbstractExtensionTestCase
{
    public function testDefaults()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('accelerator_cache.host', null);
        $this->assertContainerBuilderHasParameter('accelerator_cache.web_dir', '%kernel.root_dir%/../web');
        $this->assertContainerBuilderHasParameter('accelerator_cache.mode', 'fopen');
        $this->assertContainerBuilderHasParameter('accelerator_cache.curl_opts', array());
    }

    public function testHost()
    {
        $this->load(array('host' => 'https://example.com'));

        $this->assertContainerBuilderHasParameter('accelerator_cache.host', 'https://example.com');
    }

    public function testHostWithoutScheme()
    {
        $this->load(array('host' => 'example.com'));

        $this->assertContainerBuilderHasParameter('accelerator_cache.host', 'http://example.com');
    }

    public function testCurlMode()
    {
        $this->load(array(
            'mode' => 'curl',
            'curl_opts' => array('CURLOPT_SSL_VERIFYPEER' => false)
        ));

        $this->assertContainerBuilderHasParameter('accelerator_cache.host', null);
        $this->assertContainerBuilderHasParameter('accelerator_cache.web_dir', '%kernel.root_dir%/../web');
        $this->assertContainerBuilderHasParameter('accelerator_cache.mode', 'curl');
        $this->assertContainerBuilderHasParameter('accelerator_cache.curl_opts', array(CURLOPT_SSL_VERIFYPEER => false));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidMode()
    {
        $this->load(array('mode' => 'foo'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidCurlOption()
    {
        $this->load(array(
            'web_dir' => 'foobar',
            'curl_opts' => array('FOO' => false)
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return array(new AcceleratorCacheExtension());
    }
}
