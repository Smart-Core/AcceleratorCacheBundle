<?php

namespace SmartCore\Bundle\AcceleratorCacheBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Loads initial data
 */
class AcceleratorCacheClearCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('cache:accelerator:clear')
            ->setDescription('Clears PHP Accelerator opcode and user caches.')
            ->setDefinition([])
            ->addOption('opcode', null, InputOption::VALUE_NONE, 'Clear only opcode cache')
            ->addOption('user', null, InputOption::VALUE_NONE, 'Clear only user cache')
            ->addOption('cli', null, InputOption::VALUE_NONE, 'Only clear the cache via the CLI')
            ->addOption('auth', null, InputOption::VALUE_REQUIRED, 'HTTP authentication as username:password')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clearOpcode = $input->getOption('opcode') || !$input->getOption('user');
        $clearUser = $input->getOption('user') || !$input->getOption('opcode');
        $cli = $input->getOption('cli');

        if ($cli) {
            $result = $this->clearCliCache($clearUser, $clearOpcode);

            if($result['success']) {
                $output->writeln('Cli: '.$result['message']);
            } else {
                throw new \RuntimeException($result['message']);
            }

            return;
        }

        $container = $this->getContainer();

        $webDir = $container->getParameter('accelerator_cache.web_dir');

        if (!is_dir($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir does not exist "%s"', $webDir));
        }

        if (!is_writable($webDir)) {
            throw new \InvalidArgumentException(sprintf('Web dir is not writable "%s"', $webDir));
        }

        $filename = 'apc-'.md5(uniqid().mt_rand(0, 9999999).php_uname()).'.php';
        $file = $webDir.'/'.$filename;

        $templateFile = __DIR__.'/../Resources/template.tpl';
        $template = file_get_contents($templateFile);
        $code = strtr($template, [
            '%user%' => var_export($clearUser, true),
            '%opcode%' => var_export($clearOpcode, true)
        ]);

        if (!is_writable($file)) {
            throw new \RuntimeException(sprintf('"%s" is not writable', $file));
        }
        $fH = fopen($file, 'w+');
        if ($fH === false) {
            throw new \RuntimeException(sprintf('Can\'t open "%s"', $file));
        }
        fwrite($fH, $code);
        fclose($fH);

        if (!$host = $container->getParameter('accelerator_cache.host')) {
            $host = sprintf("%s://%s", $container->getParameter('router.request_context.scheme'), $container->getParameter('router.request_context.host'));
        }

        $url = $host.'/'.$filename;
        $auth = $input->getOption('auth');

        if ($container->getParameter('accelerator_cache.mode') == 'fopen') {
            $context = null;
            if (false === is_null($auth)) {
                $context = stream_context_create(['http' => [
                    'header' => 'Authorization: Basic ' . base64_encode($auth),
                ]]);
            }

            $result = false;
            for ($i = 0; $i < 5; $i++) {
                if ($result = @file_get_contents($url, false, $context)) {
                    break;
                } else {
                    sleep(1);
                }
            }

            if (!$result) {
                unlink($file);
                throw new \RuntimeException(sprintf('Unable to read "%s", does the host locally resolve?', $url));
            }
        }
        else {
            $ch = curl_init($url);

            $curlOpts = $container->getParameter('accelerator_cache.curl_opts');
            curl_setopt_array($ch, array_replace($curlOpts, [
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FAILONERROR => true
            ]));

            if (false === is_null($auth)) {
                curl_setopt($ch, CURLOPT_USERPWD, $auth);
            }

            $result = curl_exec($ch);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                unlink($file);
                throw new \RuntimeException(sprintf('Curl error reading "%s": %s', $url, $error));
            }
            curl_close($ch);
        }

        $result = json_decode($result, true);
        unlink($file);

        if($result['success']) {
            $output->writeln('Web: '.$result['message']." Reset attempts: ".(empty($i) ? 1 : $i+1).'.');
        } else {
            $output->writeln('Accelerator Cache clear status: failure.');
        }
    }

    protected function clearCliCache($clearUser, $clearOpcode)
    {
        $success = true;
        $message = '';

        if (function_exists('apc_clear_cache')) {
            if ($clearUser) {
                if (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '>=') && apc_clear_cache()) {
                    $message .= ' APC User Cache: success.';
                } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('user')) {
                    $message .= ' APC User Cache: success.';
                } elseif (function_exists('wincache_ucache_clear') && wincache_ucache_clear()) {
                    $message .= ' Wincache User Cache: success.';
                } else {
                    $success = false;
                    $message .= ' User Cache: failure';
                }
            }

            if ($clearOpcode) {
                if (function_exists('opcache_reset') && opcache_reset()) {
                    $message .= ' Zend OPcache: success.';
                } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('opcode')) {
                    $message .= ' APC Opcode Cache: success.';
                }
                else {
                    $success = false;
                    $message .= ' Opcode Cache: failure.';
                }
            }
        }

        return ['success' => $success, 'message' => $message];
    }
}
