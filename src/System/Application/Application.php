<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Application;

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use System\Config\ConfigRepository;
use System\Container\Container;
use System\Container\ServiceProvider\AbstractServiceProvider;
use System\Container\ServiceProvider\ServiceProvider;
use System\Http\Request;
use System\Http\Exceptions\HttpException;
use System\Support\Vite;
use System\View\Templator;

use function array_map;
use function count;
use function define;
use function defined;
use function file_exists;
use function in_array;
use function rtrim;

/**
 * Application class.
 *
 * This `Application` class represents the main entry point of the Omega framework.
 * It manages the application's lifecycle, including configuration, routing, and
 * handling HTTP requests.
 *
 * @category  System
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Application extends Container implements ApplicationInterface
{
    use ApplicationSingletonTrait;

    /** @var string The base path of the application. */
    private string $basePath = '';

    /** @var ServiceProvider[] List of all registered service providers. */
    private array $providers = [];

    /** @var ServiceProvider[] List of booted service providers. */
    private array $bootedProviders = [];

    /** @var ServiceProvider[] List of loaded service providers. */
    private array $loadedProviders = [];

    /** @var bool Indicates whether the application has been booted. */
    private bool $isBooted = false;

    /** @var bool Indicates whether the application has been bootstrapped. */
    private bool $isBootstrapped = false;

    /** @var callable[] List of registered termination callbacks. */
    private array $terminateCallback = [];

    /** @var callable[] List of registered callbacks to execute before booting completes. */
    protected array $bootingCallbacks = [];

    /** @var callable[] List of registered callbacks to execute after booting completes. */
    protected array $bootedCallbacks = [];

    /**
     * Application constructor.
     *
     * @param string $basePath Hods the base path of the application.
     * @return void
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        parent::__construct();

        // set base path
        $this->setBasePath($basePath);
        $this->setConfigPath(
            $_ENV['CONFIG_PATH']
                ?? static::DS
            . 'app'
            . static::DS
            . 'config'
            . static::DS
        );

        $this->setBaseBinding();

        $this->register(ServiceProvider::class);

        $this->registerAlias();
    }

    /**
     * Register base bindings int the container.
     *
     * @return void
     */
    protected function setBaseBinding(): void
    {
        $this->set('app', $this);
        $this->set(Application::class, $this);
        $this->set(Container::class, $this);

        $this->set(
            PackageManifest::class,
            fn () => new PackageManifest(
                $this->basePath,
                $this->getApplicationCachePath()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig(ConfigRepository $config): void
    {
        // give access to get config directly
        $this->set('config', fn (): ConfigRepository => $config);

        // base env
        $this->set('environment', $config['APP_ENV'] ?? $config['ENVIRONMENT']);
        $this->set('app.debug', $config['APP_DEBUG'] === 'true');
        // application path
        $this->setAppPath($this->getBasePath());
        $this->setModelPath($config['MODEL_PATH']);
        $this->setViewPath($config['VIEW_PATH']);
        $this->setViewPaths($config['VIEW_PATHS']);
        $this->setControllerPath($config['CONTROLLER_PATH']);
        $this->setServicesPath($config['SERVICES_PATH']);
        $this->setComponentPath($config['COMPONENT_PATH']);
        $this->setCommandPath($config['COMMAND_PATH']);
        $this->setCachePath($config['CACHE_PATH']);
        $this->setCompiledViewPath($config['COMPILED_VIEW_PATH']);
        $this->setMiddlewarePath($config['MIDDLEWARE']);
        $this->setProviderPath($config['SERVICE_PROVIDER']);
        $this->setMigrationPath($config['MIGRATION_PATH']);
        $this->setPublicPath($config['PUBLIC_PATH']);
        $this->setSeederPath($config['SEEDER_PATH']);
        $this->setStoragePath($config['STORAGE_PATH']);
        // other config
        $this->set('config.pusher_id', $config['PUSHER_APP_ID']);
        $this->set('config.pusher_key', $config['PUSHER_APP_KEY']);
        $this->set('config.pusher_secret', $config['PUSHER_APP_SECRET']);
        $this->set('config.pusher_cluster', $config['PUSHER_APP_CLUSTER']);
        $this->set('config.view.extensions', $config['VIEW_EXTENSIONS']);
        // load provider
        $this->providers = $config['PROVIDERS'];
        $this->defined($config->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfig(): array
    {
        return [
            'BASEURL'               => '/',
            'time_zone'             => 'UTC',
            'APP_KEY'               => '',
            'ENVIRONMENT'           => 'dev',
            'APP_DEBUG'             => 'false',
            'BCRYPT_ROUNDS'         => 12,
            'CACHE_STORE'           => 'file',

            'COMMAND_PATH'          => static::DS . 'app' . static::DS . 'Commands' . static::DS,
            'CONTROLLER_PATH'       => static::DS . 'app' . static::DS . 'Controllers' . static::DS,
            'MODEL_PATH'            => static::DS . 'app' . static::DS . 'Models' . static::DS,
            'MIDDLEWARE'            => static::DS . 'app' . static::DS . 'Middlewares' . static::DS,
            'SERVICE_PROVIDER'      => static::DS . 'app' . static::DS . 'Providers' . static::DS,
            'CONFIG'                => static::DS . 'app' . static::DS . 'config' . static::DS,
            'SERVICES_PATH'         => static::DS . 'app' . static::DS . 'services' . static::DS,
            'VIEW_PATH'             => static::DS . 'resources' . static::DS . 'views' . static::DS,
            'COMPONENT_PATH'        => static::DS . 'resources' . static::DS . 'components' . static::DS,
            'STORAGE_PATH'          => static::DS . 'storage' . static::DS . 'app' . static::DS,
            'CACHE_PATH'            => static::DS . 'storage' . static::DS . 'app' . static::DS . 'cache' . static::DS,
            'CACHE_VIEW_PATH'       => static::DS . 'storage' . static::DS . 'app' . static::DS . 'view' . static::DS,
            'PUBLIC_PATH'           => static::DS . 'public' . static::DS,
            'MIGRATION_PATH'        => static::DS . 'database' . static::DS . 'migration' . static::DS,
            'SEEDER_PATH'           => static::DS . 'database' . static::DS . 'seeders' . static::DS,

            'PROVIDERS'             => [
                // provider class name
            ],

            // db config
            'DB_HOST'               => 'localhost',
            'DB_USER'               => 'root',
            'DB_PASS'               => '',
            'DB_NAME'               => '',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST'            => '127.0.0.1',
            'REDIS_PASS'            => '',
            'REDIS_PORT'            => 6379,

            // memcached
            'MEMCACHED_HOST'        => '127.0.0.1',
            'MEMCACHED_PASS'        => '',
            'MEMCACHED_PORT'        => 6379,

            // view config
            'VIEW_PATHS' => [
                static::DS . 'resources' . static::DS . 'views' . static::DS,
            ],
            'VIEW_EXTENSIONS' => [
                '.template.php',
                '.php',
            ],
            'COMPILED_VIEW_PATH' => static::DS . 'storage' . static::DS . 'app' . static::DS . 'view' . static::DS,
        ];
    }

    /**
     * Define constants for legacy API configurations.
     *
     * @param array<string, string> $config Configuration array for legacy API settings.
     * @return void
     */
    private function defined(array $config): void
    {
        // Redis
        defined('REDIS_HOST') || define('REDIS_HOST', $config['REDIS_HOST']);
        defined('REDIS_PASS') || define('REDIS_PASS', $config['REDIS_PASS']);
        defined('REDIS_PORT') || define('REDIS_PORT', $config['REDIS_PORT']);

        // Memcache
        defined('MEMCACHED_HOST') || define('MEMCACHED_HOST', $config['MEMCACHED_HOST']);
        defined('MEMCACHED_PASS') || define('MEMCACHED_PASS', $config['MEMCACHED_PASS']);
        defined('MEMCACHED_PORT') || define('MEMCACHED_PORT', $config['MEMCACHED_PORT']);
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath(string $path): self
    {
        $this->basePath = $path;
        $this->set('path.base', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAppPath(string $path): self
    {
        $this->set('path.app', $path . static::DS . 'app' . static::DS);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setModelPath(string $path): self
    {
        $this->set('path.model', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPath(string $path): self
    {
        $this->set('path.view', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPaths(array $paths): self
    {
        $viewPaths = array_map(fn ($path) => $this->basePath . $path, $paths);
        $this->set('paths.view', $viewPaths);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerPath(string $path): self
    {
        $this->set('path.controller', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServicesPath(string $path): self
    {
        $this->set('path.services', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponentPath(string $path): self
    {
        $this->set('path.component', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandPath(string $path): self
    {
        $this->set('path.command', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoragePath(string $path): self
    {
        $this->set('path.storage', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCachePath(string $path): self
    {
        $this->set('path.cache', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompiledViewPath(string $path): self
    {
        $this->set('path.compiled_view_path', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigPath(string $path): self
    {
        $this->set('path.config', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewarePath(string $path): self
    {
        $this->set('path.middleware', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderPath(string $path): self
    {
        $this->set('path.provider', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMigrationPath(string $path): self
    {
        $this->set('path.migration', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeederPath(string $path): self
    {
        $this->set('path.seeder', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicPath(string $path): self
    {
        $this->set('path.public', $this->basePath . $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationPath(): string
    {
        return $this->get('path.app');
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationCachePath(): string
    {
        return rtrim($this->getBasePath(), static::DS) . static::DS . 'bootstrap' . static::DS . 'cache' . static::DS;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelPath(): string
    {
        return $this->get('path.model');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(): string
    {
        return $this->get('path.view');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPaths(): array
    {
        return $this->get('paths.view');
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerPath(): string
    {
        return $this->get('path.controller');
    }

    /**
     * {@inheritdoc}
     */
    public function getServicesPath(): string
    {
        return $this->get('path.services');
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentPath(): string
    {
        return $this->get('path.component');
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandPath(): string
    {
        return $this->get('path.command');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(): string
    {
        return $this->get('path.storage');
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath(): string
    {
        return $this->get('path.cache');
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledViewPath(): string
    {
        return $this->get('path.compiled_view_path');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewarePath(): string
    {
        return $this->get('path.middleware');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderPath(): string
    {
        return $this->get('path.provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationPath(): string
    {
        return $this->get('path.migration');
    }

    /**
     * {@inheritdoc}
     */
    public function getSeederPath(): string
    {
        return $this->get('path.seeder');
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(): string
    {
        return $this->get('path.public');
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    /**
     * {@inheritdoc}
     */
    public function isDebugMode(): bool
    {
        return $this->get('app.debug');
    }

    /**
     * {@inheritdoc}
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'prod';
    }

    /**
     * {@inheritdoc}
     */
    public function isDev(): bool
    {
        return $this->getEnvironment() === 'dev';
    }

    /**
     * {@inheritdoc}
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    /**
     * {@inheritdoc}
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrapped): void
    {
        $this->isBootstrapped = true;

        foreach ($bootstrapped as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bootProvider(): void
    {
        if ($this->isBooted) {
            return;
        }

        $this->callBootCallbacks($this->bootingCallbacks);

        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->bootedProviders)) {
                continue;
            }

            $this->call([$provider, 'boot']);
            $this->bootedProviders[] = $provider;
        }

        $this->callBootCallbacks($this->bootedCallbacks);

        $this->isBooted = true;
    }

    /**
     * {@inheritdoc}
     */
    public function registerProvider(): void
    {
        foreach ($this->getMergeProviders() as $provider) {
            if (in_array($provider, $this->loadedProviders)) {
                continue;
            }

            $this->call([$provider, 'register']);

            $this->loadedProviders[] = $provider;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function callBootCallbacks(array $bootCallBacks): void
    {
        $index = 0;

        while ($index < count($bootCallBacks)) {
            $this->call($bootCallBacks[$index]);

            $index++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bootingCallback(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function bootedCallback(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->call($callback);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->providers         = [];
        $this->loadedProviders  = [];
        $this->bootedProviders  = [];
        $this->terminateCallback = [];
        $this->bootingCallbacks = [];
        $this->bootedCallbacks  = [];

        parent::flush();
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $provider): AbstractServiceProvider
    {
        $providerClassName = $provider;
        $provider          = new $provider($this);

        $provider->register();
        $this->loadedProviders[] = $providerClassName;

        if ($this->isBooted) {
            $provider->boot();
            $this->bootedProviders[] = $providerClassName;
        }

        $this->providers[] = $providerClassName;

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function registerTerminate(callable $terminateCallback): self
    {
        $this->terminateCallback[] = $terminateCallback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $index = 0;

        while ($index < count($this->terminateCallback)) {
            $this->call($this->terminateCallback[$index]);

            $index++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists($this->getStoragePath() . 'app' . static::DS . 'maintenance.php');
    }

    /**
     * {@inheritdoc}
     */
    public function getDownData(): array
    {
        $default = [
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ];

        if (false === file_exists($down = $this->getStoragePath() . 'app' . static::DS . 'down')) {
            return $default;
        }

        /** @var array<string, string|int|null> $config */
        $config = include $down;

        foreach ($config as $key => $value) {
            $default[$key] = $value;
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Register container aliases.
     *
     * @return void
     * @throws Exception
     */
    protected function registerAlias(): void
    {
        foreach (
            [
            'request'       => [Request::class],
            'view.instance' => [Templator::class],
            'vite.gets'     => [Vite::class],
            'config'        => [ConfigRepository::class],
            ] as $abstract => $aliases
        ) {
            foreach ($aliases as $alias) {
                $this->alias($abstract, $alias);
            }
        }
    }

    /**
     * Merge application service providers with vendor package providers.
     *
     * @return AbstractServiceProvider[] Return the merged list of service providers.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required service provider is not found.
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }
}
