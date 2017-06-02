Provide a command line to clear PHP Accelerator cache from the console.

The problem with Accelerator cache (like APC, Wincache, Opcache) is that it's impossible to clear it from command line.
Because even if you enable APC for PHP CLI, it's a different instance than,
say, your Apache PHP or PHP-CGI APC instance.

The trick here is to create a file in the web dir, execute it through HTTP,
then remove it.

Prerequisite
============

If you want to clear Apache part of APC, you will need to enable `allow_url_fopen` in `php.ini` to allow opening of URL
object-like files, or set the curl option.



Installation
============

  1. Add it to your composer.json:

```json
{
    "require": {
        "smart-core/accelerator-cache-bundle": "dev-master"
    }
}
```

    or:

```sh
composer require smart-core/accelerator-cache-bundle
composer update smart-core/accelerator-cache-bundle
```

  2. Add this bundle to your application kernel:

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new SmartCore\Bundle\AcceleratorCacheBundle\AcceleratorCacheBundle(),
        // ...
    );
}
```

  3. Configure `accelerator_cache` service:

```yml
# app/config/config.yml
accelerator_cache:
    host: http://example.com
    web_dir: %kernel.root_dir%/../web
```

  4. If you want to use curl rather than fopen set the following option:

```yml
# app/config/config.yml
accelerator_cache:
    ...
    mode: curl
#   additional options can be passed to the command
#   curl_opts:
#       CURLOPT_*: custom_value
```

Usage
=====

Clear all Accelerator cache (opcode+user):

    $ php app/console cache:accelerator:clear

Clear only opcode cache:

    $ php app/console cache:accelerator:clear --opcode

Clear only user cache:

    $ php app/console cache:accelerator:clear --user

Clear the CLI cache (opcode+user):

    $ php app/console cache:accelerator:clear --cli


Composer usage
==============

To automatically clear accelerator cache after each composer install / composer update, you can add a script handler to your project's composer.json :

```json
        "post-install-cmd": [
            "SmartCore\\Bundle\\AcceleratorCacheBundle\\Composer\\ScriptHandler::clearCache"
        ],
        "post-update-cmd": [
            "SmartCore\\Bundle\\AcceleratorCacheBundle\\Composer\\ScriptHandler::clearCache"
        ]
```

+You can specify command arguments in the `extra` section:

- `--opcode` (to clean only opcode cache):

```json
        "extra": {
          "accelerator-cache-opcode": "yes"
        }
```

- `--user` (to clean only user cache):

```json
        "extra": {
          "accelerator-cache-user": "yes"
        }
```

- `--cli` (to only clear cache via the CLI):

```json
        "extra": {
          "accelerator-cache-cli": "yes"
        }
```

- `--auth` (HTTP authentification):

```json
        "extra": {
          "accelerator-cache-auth": "username:password"
        }
```


Capifony usage
==============

To automatically clear apc cache after each capifony deploy you can define a custom task

```ruby
namespace :symfony do
  desc "Clear accelerator cache"
  task :clear_accelerator_cache do
    capifony_pretty_print "--> Clear accelerator cache"
    run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} cache:accelerator:clear #{console_options}'"
    capifony_puts_ok
  end
end
```

and add these hooks

```ruby
# clear accelerator cache
after "deploy", "symfony:clear_accelerator_cache"
after "deploy:rollback:cleanup", "symfony:clear_accelerator_cache"
```

Nginx configuration
===================

If you are using nginx and limiting PHP scripts that you are passing to fpm you need to allow 'apc' prefixed php files. Otherwise your web server will return the requested PHP file as text and the system won't be able to clear the accelerator cache.

Example configuration:
```
# Your virtual host
server {
  ...
  location ~ ^/(app|app_dev|apc-.*)\.php(/|$) { { # This will allow accelerator cache (apc-{MD5HASH}.php) files to be processed by fpm
    fastcgi_pass                127.0.0.1:9000;
    ...
``` 
