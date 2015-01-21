<?php
$message = 'Clear PHP Accelerator Cache...';
$success = true;

if(%user%) {
    if (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '>=') && apc_clear_cache()) {
        $message .= ' APC User Cache: success.';
    } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('user')) {
        $message .= ' APC User Cache: success.';
    } elseif (function_exists('wincache_ucache_clear') && wincache_ucache_clear()) {
        $message .= ' Wincache User Cache: success.';
    } else {
        $success = false;
        $message .= ' User Cache: failure.';
    }
}

if(%opcode%) {
    if (function_exists('opcache_reset') && opcache_reset()) {
        $message .= ' Zend OPcache: success.';
    } elseif (function_exists('apc_clear_cache') && version_compare(PHP_VERSION, '5.5.0', '<') && apc_clear_cache('opcode')) {
        $message .= ' APC Opcode Cache: success.';
    } else {
        $success = false;
        $message .= ' Opcode Cache: failure.';
    }
}

die(json_encode(['success' => $success, 'message' => $message]));
