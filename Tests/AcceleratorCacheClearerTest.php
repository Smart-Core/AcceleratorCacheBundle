<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Tests;

use SmartCore\Bundle\AcceleratorCacheBundle\AcceleratorCacheClearer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AcceleratorCacheClearerTest extends \PHPUnit_Framework_TestCase
{
    public function testClearCache()
    {
        $result = AcceleratorCacheClearer::clearCache();

        if (PHP_VERSION_ID >= 50500) {
            $this->assertFalse($result['success']);
            $this->assertSame('Clear PHP Accelerator Cache... APC User Cache: success. Opcode Cache: failure.', $result['message']);
        } else {
            $this->assertTrue($result['success']);
            $this->assertSame('Clear PHP Accelerator Cache... APC User Cache: success. APC Opcode Cache: success.', $result['message']);
        }

        $result = AcceleratorCacheClearer::clearCache(true, false);
        $this->assertTrue($result['success']);
        $this->assertSame('Clear PHP Accelerator Cache... APC User Cache: success.', $result['message']);

        $result = AcceleratorCacheClearer::clearCache(false, true);

        if (PHP_VERSION_ID >= 50500) {
            $this->assertFalse($result['success']);
            $this->assertSame('Clear PHP Accelerator Cache... Opcode Cache: failure.', $result['message']);
        } else {
            $this->assertTrue($result['success']);
            $this->assertSame('Clear PHP Accelerator Cache... APC Opcode Cache: success.', $result['message']);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidClearCacheParameters()
    {
        AcceleratorCacheClearer::clearCache(false, false);
    }
}
