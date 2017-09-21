<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @see \Sensio\Bundle\DistributionBundle\Composer\ScriptHandler
 */
class ScriptHandler
{
    private static $options = array(
        'symfony-bin-dir' => 'app',
    );

    /**
     * Clears the APC/Wincache/Opcache cache.
     *
     * @param $event Event A instance
     */
    public static function clearCache(Event $event)
    {
        $options = self::getOptions($event);
        $binDir = $options['symfony-bin-dir'];

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

        try {
            static::executeCommand($event, $binDir, 'cache:accelerator:clear'.$opcode.$user.$cli.$auth, $options['process-timeout']);
        } catch (\RuntimeException $e) {
            $event->getIO()->write('<error>'.$e->getMessage().'</error>');
        }
    }

    /**
     * @param Event $event
     * @return array
     */
    private static function getOptions(Event $event)
    {
        $options = array_merge(static::$options, $event->getComposer()->getPackage()->getExtra());

        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');

        return $options;
    }

    /**
     * @param Event $event
     * @param $consoleDir
     * @param $cmd
     * @param int $timeout
     */
    private static function executeCommand(Event $event, $consoleDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(static::getPhp(false));
        $phpArgs = implode(' ', array_map('escapeshellarg', static::getPhpArguments()));
        $console = escapeshellarg($consoleDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $process = new Process($php.($phpArgs ? ' '.$phpArgs : '').' '.$console.' '.$cmd, null, null, null, $timeout);
        $process->run(function ($type, $buffer) use ($event) { $event->getIO()->write($buffer, false); });
        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf("An error occurred when executing the \"%s\" command:\n\n%s\n\n%s", escapeshellarg($cmd), self::removeDecoration($process->getOutput()), self::removeDecoration($process->getErrorOutput())));
        }
    }

    /**
     * @param bool $includeArgs
     * @return mixed
     */
    private static function getPhp($includeArgs = true)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find($includeArgs)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

    /**
     * @return array
     */
    private static function getPhpArguments()
    {
        $ini = null;
        $arguments = array();

        $phpFinder = new PhpExecutableFinder();
        if (method_exists($phpFinder, 'findArguments')) {
            $arguments = $phpFinder->findArguments();
        }

        if ($env = getenv('COMPOSER_ORIGINAL_INIS')) {
            $paths = explode(PATH_SEPARATOR, $env);
            $ini = array_shift($paths);
        } else {
            $ini = php_ini_loaded_file();
        }

        if ($ini) {
            $arguments[] = '--php-ini='.$ini;
        }

        return $arguments;
    }

    /**
     * @param $string
     * @return mixed
     */
    private static function removeDecoration($string)
    {
        return preg_replace("/\033\[[^m]*m/", '', $string);
    }
}
