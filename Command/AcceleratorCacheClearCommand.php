<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Command;

use SmartCore\Bundle\AcceleratorCacheBundle\AcceleratorCacheClearer;
use SmartCore\Bundle\AcceleratorCacheBundle\CacheClearerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AcceleratorCacheClearCommand extends Command
{
    /**
     * @var CacheClearerService
     */
    private $cacheClearer;

    public function __construct(CacheClearerService $cacheClearer)
    {
        parent::__construct(null);

        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:accelerator:clear')
            ->setDescription('Clears PHP Accelerator opcode and user caches.')
            ->addOption('opcode', null, InputOption::VALUE_NONE, 'Clear only opcode cache')
            ->addOption('user', null, InputOption::VALUE_NONE, 'Clear only user cache')
            ->addOption('cli', null, InputOption::VALUE_NONE, 'Only clear the cache via the CLI')
            ->addOption('auth', null, InputOption::VALUE_REQUIRED, 'HTTP authentication as username:password')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearOpcode = $input->getOption('opcode') || !$input->getOption('user');
        $clearUser = $input->getOption('user') || !$input->getOption('opcode');
        $type = 'Web';

        if ($input->getOption('cli')) {
            $type = 'cli';
            $result = AcceleratorCacheClearer::clearCache($clearUser, $clearOpcode);
        } else {
            $result = $this->cacheClearer->clearCache($clearUser, $clearOpcode, $input->getOption('auth'));
        }

        if (!$result['success']) {
            throw new \RuntimeException($result['message']);
        }

        $output->writeln(sprintf('(%s) %s', $type, $result['message']));
    }
}
