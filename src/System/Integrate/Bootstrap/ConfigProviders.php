<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use System\Application\Application;
use System\Config\ConfigRepository;

class ConfigProviders
{
    public function bootstrap(Application $app): void
    {
        $config_path = $app->getConfigPath();
        $config      =  [];
        $has_cache   = false;
        if (file_exists($file = $app->getApplicationCachePath() . 'config.php')) {
            $config    = array_merge($config, require $file);
            $has_cache = true;
        }

        if (false === $has_cache) {
            foreach (glob("{$config_path}*.php") as $path) {
                foreach (include $path as $key => $value) {
                    $config[$key] = $value;
                }
            }
        }

        $app->loadConfig(new ConfigRepository($config));

        date_default_timezone_set($config['time_zone'] ?? 'UTC');
    }
}
