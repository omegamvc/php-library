<?php

declare(strict_types=1);

namespace System\Integrate\Bootstrap;

use System\Application\Application;
use System\Integrate\ConfigRepository;

class ConfigProviders
{
    public function bootstrap(Application $app): void
    {
        $configPath  = $app->getConfigPath();
        $cachePath   = $app->getApplicationCachePath();
        $timestamp   = date('Y-m-d_H-i-s');
        $cacheFile   = "{$cachePath}{$timestamp}_config.php";
        $config      = [];

        // Load all configuration files from the config directory
        foreach (glob("{$configPath}*.php") as $path) {
            foreach (include $path as $key => $value) {
                $config[$key] = $value;
            }
        }

        // Save configuration to cache
        file_put_contents($cacheFile, '<?php return ' . var_export($config, true) . ';');

        // Load configuration into the application
        $app->loadConfig(new ConfigRepository($config));
    }
}
