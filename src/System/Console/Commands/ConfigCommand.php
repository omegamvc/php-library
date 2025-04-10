<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Application\Application;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Support\Bootstrap\ConfigProviders;
use System\Config\ConfigRepository;

use function file_exists;
use function file_put_contents;
use function System\Console\fail;
use function System\Console\ok;
use function unlink;
use function var_export;

class ConfigCommand extends Command
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'config:cache',
            'fn'      => [ConfigCommand::class, 'main'],
        ], [
            'pattern' => 'config:clear',
            'fn'      => [ConfigCommand::class, 'clear'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'config:cache' => 'Build cache application config',
                'config:clear' => 'Remove cached application config',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    public function main(): int
    {
        $app = Application::getInstance();
        (new ConfigProviders())->bootstrap($app);

        $this->clear();
        $config       = $app->get(ConfigRepository::class)->toArray();
        $cachedConfig = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'config.php', $cachedConfig)) {
            ok('Config file has successfully created.')->out();

            return 0;
        }
        fail('Cant build config cache.')->out();

        return 1;
    }

    public function clear(): int
    {
        if (file_exists($file = Application::getInstance()->getApplicationCachePath() . 'config.php')) {
            @unlink($file);
            ok('Clear config file has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
