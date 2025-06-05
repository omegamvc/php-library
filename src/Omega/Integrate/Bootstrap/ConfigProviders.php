<?php

declare(strict_types=1);

namespace Omega\Integrate\Bootstrap;

use Omega\Integrate\Application;
use Omega\Integrate\ConfigRepository;

class ConfigProviders
{
    public function bootstrap(Application $app): void
    {
        $config_path = $app->configPath();
        $config      =  $app->defaultConfigs();
        $has_cache   = false;
        if (file_exists($file = $app->getApplicationCachePath() . '.php')) {
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
