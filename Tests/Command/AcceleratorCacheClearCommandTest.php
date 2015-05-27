<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Tests\Command;

use SmartCore\Bundle\AcceleratorCacheBundle\Command\AcceleratorCacheClearCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AcceleratorCacheClearCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testClearCache()
    {
        $cacheClearer = $this->getMock('SmartCore\Bundle\AcceleratorCacheBundle\CacheClearerService', array(), array(), '', false);
        $cacheClearer->expects($this->once())
            ->method('clearCache')
            ->with(true, true)
            ->willReturn(array('success' => true, 'message' => 'foobar'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('get')
            ->with('accelerator_cache.clearer')
            ->willReturn($cacheClearer);

        $this->assertContains('(Web) foobar', $this->createCommandTester(array(), $container)->getDisplay());
    }

    public function testCliClearUser()
    {
        $commandTester = $this->createCommandTester(array('--cli' => true, '--user' => true));

        $this->assertContains('(cli) Clear PHP Accelerator Cache... APC User Cache: success.', $commandTester->getDisplay());
    }

    public function testCliClearOpcode()
    {
        if (PHP_VERSION_ID >= 50500) {
            $this->setExpectedException('\RuntimeException', 'Clear PHP Accelerator Cache... Opcode Cache: failure.');
            $this->createCommandTester(array('--cli' => true, '--opcode' => true));
        } else {
            $commandTester = $this->createCommandTester(array('--cli' => true, '--opcode' => true));
            $this->assertContains('(cli) Clear PHP Accelerator Cache... APC Opcode Cache: success.', $commandTester->getDisplay());
        }
    }

    private function createCommandTester(array $options = array(), $container = null)
    {
        $command = new AcceleratorCacheClearCommand();

        if ($container) {
            $command->setContainer($container);
        }

        $application = new Application();
        $application->add($command);

        $command = $application->find('cache:accelerator:clear');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array_merge(array('command' => $command->getName()), $options));

        return $commandTester;
    }
}
