<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SymfonyScriptHandler;

class ScriptHandler extends SymfonyScriptHandler
{
    /**
     * Clears the APC/Wincache/Opcache cache.
     *
     * @param $event CommandEvent A instance
     */
    public static function clearCache(CommandEvent $event)
    {
        $options = parent::getOptions($event);
        //$consoleDir = parent::getConsoleDir($event, 'clear the PHP Accelerator cache');
        $appDir = $options['symfony-app-dir'];

        if (null === $appDir) {
            return;
        }

        $opcode = '';
        if (array_key_exists('accelerator-cache-opcode', $options)) {
            $opcode .= ' --opcode';
        }

        $user = '';
        if (array_key_exists('accelerator-cache-user', $options)) {
            $user .= ' --user';
        }

        $cli = '';
        if (array_key_exists('accelerator-cache-cli', $options)) {
            $cli .= ' --cli';
        }

        $auth = '';
        if (array_key_exists('accelerator-cache-auth', $options)) {
            $auth .= ' --auth '.escapeshellarg($options['accelerator-cache-auth']);
        }

        static::executeCommand($event, $appDir, 'cache:accelerator:clear'.$opcode.$user.$cli.$auth, $options['process-timeout']);
    }
}
