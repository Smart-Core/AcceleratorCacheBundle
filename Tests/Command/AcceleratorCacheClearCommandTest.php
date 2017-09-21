<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Tests\Command;

use SmartCore\Bundle\AcceleratorCacheBundle\Command\AcceleratorCacheClearCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AcceleratorCacheClearCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testClearCache()
    {
        $cacheClearer = $this->createCacheClearer();
        $cacheClearer->expects($this->once())
            ->method('clearCache')
            ->with(true, true)
            ->willReturn(array('success' => true, 'message' => 'foobar'));

        $this->assertContains('(Web) foobar', $this->createCommandTester($cacheClearer, array())->getDisplay());
    }

    public function testCliClearUser()
    {
        $commandTester = $this->createCommandTester($this->createCacheClearer(), array('--cli' => true, '--user' => true));

        $this->assertContains('(cli) Clear PHP Accelerator Cache... APC User Cache: success.', $commandTester->getDisplay());
    }

    public function testCliClearOpcode()
    {
        if (PHP_VERSION_ID >= 50500) {
            $this->expectException('\RuntimeException');
            $this->expectExceptionMessage('Clear PHP Accelerator Cache... Opcode Cache: failure.');
            $this->createCommandTester($this->createCacheClearer(), array('--cli' => true, '--opcode' => true));
        } else {
            $commandTester = $this->createCommandTester(array('--cli' => true, '--opcode' => true));
            $this->assertContains('(cli) Clear PHP Accelerator Cache... APC Opcode Cache: success.', $commandTester->getDisplay());
        }
    }

    private function createCacheClearer()
    {
        return $this->createMock('SmartCore\Bundle\AcceleratorCacheBundle\CacheClearerService');
    }

    private function createCommandTester($cacheClearer, array $options = array())
    {
        $command = new AcceleratorCacheClearCommand($cacheClearer);

        $application = new Application();
        $application->add($command);

        $command = $application->find('cache:accelerator:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(array('command' => $command->getName()), $options));

        return $commandTester;
    }
}
