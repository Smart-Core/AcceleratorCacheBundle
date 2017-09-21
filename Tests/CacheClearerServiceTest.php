<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Tests;

use SmartCore\Bundle\AcceleratorCacheBundle\CacheClearerService;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CacheClearerServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Curl error reading
     */
    public function testClearCacheCurlInvalidHost()
    {
        $service = new CacheClearerService('http://localhost', $this->getTempDir(), 'baz', CacheClearerService::MODE_CURL);
        $service->clearCache();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to read
     */
    public function testClearCacheFopenInvalidHost()
    {
        $service = new CacheClearerService('http://localhost', $this->getTempDir(), 'baz');
        $service->clearCache();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Web dir is not writable "/"
     */
    public function testClearCacheNonWritableDir()
    {
        $service = new CacheClearerService('http://localhost', '/', 'baz');
        $service->clearCache();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Web dir does not exist "/foobarbaz"
     */
    public function testClearCacheNonExistantDir()
    {
        $service = new CacheClearerService('http://localhost', '/foobarbaz', 'baz');
        $service->clearCache();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidMode()
    {
        $service = new CacheClearerService('foo', 'bar', 'baz', 'foo');
    }

    protected function setUp()
    {
        parent::setUp();

        $filesystem = new Filesystem();
        $filesystem->remove($this->getTempDir());
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->assertCount(2, scandir($this->getTempDir()));
    }

    private function getTempDir()
    {
        $dir = sys_get_temp_dir().'/accelerator-cache-bundle';

        if (!is_dir($dir)) {
            mkdir($dir);
        }

        return $dir;
    }
}
