<?php

declare(strict_types=1);

namespace System\Application;

use App\Providers\AppServiceProvider;
use App\Providers\CacheServiceProvider;
use App\Providers\DatabaseServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\ViewServiceProvider;
use System\Container\Container;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Http\Request\Request;
use System\Config\ConfigRepository;
use System\Http\Exceptions\HttpException;
use System\Container\ServiceProvider\AdditionalServiceProvider;
use System\Container\ServiceProvider\AbstractServiceProvider;
use System\Support\Vite;
use System\Support\PackageManifest;
use System\Support\Path;
use System\View\Templator;

use function count;
use function file_exists;
use function in_array;

abstract class AbstractApplication extends Container implements ApplicationInterface
{
    /** @var string Holds the application app path. */
    protected string $appPath = '';

    /** @var string Holds the project_root base path. */
    protected string $basePath;

    /** @var string Holds the bin directory base path. */
    protected string $binPath = '';

    /** @var string Holds the application cache path. */
    protected string $appCachePath = '';

    /** @var string Holds the config path. */
    protected string $configPath = '';

    /** @var string Holds the database path. */
    protected string $databasePath = '';

    /** @var string Holds the public path. */
    protected string $publicPath = '';

    /** @var string Holds the resources path. */
    protected string $resourcesPath = '';

    /** @var string Holds the routes path. */
    protected string $routesPath = '';

    /** @var string Holds the storage path. */
    protected string $storagePath = '';

    /** @var string Holds the test path. */
    protected string $testPath = '';

    /** @var string Holds the vendor path. */
    protected string $vendorPath = '';

    /** @var \System\Container\ServiceProvider\AbstractServiceProvider[] Holds an array of all service provider. */
    private array $providers = [
        AppServiceProvider::class,
        RouteServiceProvider::class,
        DatabaseServiceProvider::class,
        ViewServiceProvider::class,
        CacheServiceProvider::class,
    ];

    /** @var \System\Container\ServiceProvider\AbstractServiceProvider[] Holds an array of booted service provider. */
    private array $bootedProviders = [];

    /** @var AbstractServiceProvider[] Holds an array of loaded service provider. */
    private array $loadedProviders = [];

    /** @var bool Detect if the application has been booted. */
    private bool $isBooted = false;

    /** @var bool Detect if the application has been bootstrapped.*/
    private bool $isBootstrapped = false;

    /** @var callable[] Register an array of terminate callback. */
    private array $terminateCallback = [];

    /** @var callable[] Holds ana array of registered booting callback. */
    protected array $bootingCallbacks = [];

    /** @var callable[] Holds an array of registered booted callback. */
    protected array $bootedCallbacks = [];

    /**
     * Contractor.
     *
     * @param string|null $basePath application path
     * @return void
     */
    protected function __construct(?string $basePath = null)
    {
        parent::__construct();

        $this->basePath = rtrim($basePath ?? $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__, 5), '\/');
        Path::init($basePath);

        $this->setBasePath($this->basePath);
        $this->setBaseBinding();
        $this->register(AdditionalServiceProvider::class);
        $this->registerAlias();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(string $projectVersion = ''): string
    {
        return $projectVersion !== '' ? $projectVersion : static::PROJECT_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(string $projectName= ''): string
    {
        return $projectName !== '' ? $projectName : static::PROJECT_NAME;
    }

    /**
     * Register base binding container.
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
                $this->getApplicationCachePath(),
                $this->getVendorPath()
            )
        );
    }

    /**
     * Load and set Configuration to application.
     *
     * @param \System\Config\ConfigRepository $configs
     * @return void
     */
    public function loadConfig(ConfigRepository $configs): void
    {
        // give access to get config directly
        $this->set('config', fn (): ConfigRepository => $configs);

        // base env
        $this->set('environment', $configs['APP_ENV'] ?? $configs['ENVIRONMENT']);
        $this->set('app.debug', $configs['APP_DEBUG'] === 'true');
        $this->set('config.pusher_id', $configs['PUSHER_APP_ID']);
        $this->set('config.pusher_key', $configs['PUSHER_APP_KEY']);
        $this->set('config.pusher_secret', $configs['PUSHER_APP_SECRET']);
        $this->set('config.pusher_cluster', $configs['PUSHER_APP_CLUSTER']);
        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath(string $path = ''): string
    {
        return $this->joinPaths($this->basePath, $path);
    }

    /**
     * Set the base path.
     *
     * @param string $basePath Holds the base path.
     * @return self
     */
    public function setBasePath(string $basePath): self
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPaths();

        return $this;
    }

    /**
     * Bind the application path with the container.
     *
     * @return void
     */
    public function bindPaths(): void
    {
        $this->instance('path.app', $this->getAppPath());
        $this->instance('path.bin', $this->getBinPath());
        $this->instance('path.application.cache', $this->getApplicationCachePath());
        $this->instance('path.config', $this->getConfigPath());
        $this->instance('path.database', $this->getDatabasePath());
        $this->instance('path.public', $this->getPublicPath());
        $this->instance('path.resources', $this->getResourcesPath());
        $this->instance('path.routes', $this->getRoutesPath());
        $this->instance('path.storage', $this->getStoragePath());
        $this->instance('path.test', $this->getTestPath());
        $this->instance('path.vendor', $this->getVendorPath());
    }

    /**
     * Detect application environment.
     *
     * @return string
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function getEnvironment(): string
    {
        return $this->get('environment');
    }

    /**
     * Detect application debug enable.
     *
     * @return bool
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function isDebugMode(): bool
    {
        return $this->get('app.debug');
    }

    /**
     * Detect application production mode.
     *
     * @return bool
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'prod';
    }

    /**
     * Detect application development mode.
     *
     * @return bool
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function isDev(): bool
    {
        return $this->getEnvironment() === 'dev';
    }

    /**
     * Detect application has been booted.
     */
    public function isBooted(): bool
    {
        return $this->isBooted;
    }

    /**
     * Detect application has been bootstrapped.
     */
    public function isBootstrapped(): bool
    {
        return $this->isBootstrapped;
    }

    // core region

    /**
     * Bootstrapper.
     *
     * @param array<int, class-string> $bootstrappers
     * @return void
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $this->isBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Boot service provider.
     *
     * @return void
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
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
     * Register service providers.
     *
     * @return void
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
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
     * Call the registered booting callbacks.
     *
     * @param callable[] $bootCallBacks
     * @return void
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
     * Add booting call back, call before boot is calling.
     *
     * @param callable $callback
     * @return void
     */
    public function bootingCallback(callable $callback): void
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Add booted call back, call after boot is called.
     *
     * @param callable $callback
     * @return void
     */
    public function bootedCallback(callable $callback): void
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->call($callback);
        }
    }

    /**
     * Flush or reset application (static).
     */
    public function flush(): void
    {
        $this->providers         = [];
        $this->loadedProviders   = [];
        $this->bootedProviders   = [];
        $this->terminateCallback = [];
        $this->bootingCallbacks  = [];
        $this->bootedCallbacks   = [];

        parent::flush();
    }

    /**
     * Register service provider.
     *
     * @param string $provider Class-name service provider
     * @return \System\Container\ServiceProvider\AbstractServiceProvider
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
     * Register terminating callbacks.
     *
     * @param callable $terminateCallback
     * @return self
     */
    public function registerTerminate(callable $terminateCallback): self
    {
        $this->terminateCallback[] = $terminateCallback;

        return $this;
    }

    /**
     * Terminate the application.
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
     * Determinate application maintenance mode.
     */
    public function isDownMaintenanceMode(): bool
    {
        return file_exists($this->getStoragePath() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Get down maintenance file config.
     *
     * @return array<string, string|int|null>
     */
    public function getDownData(): array
    {
        $default = [
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ];

        if (false === file_exists($down = $this->getStoragePath() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
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
     * Abort application to http exception.
     *
     * @param array<string, string> $headers
     *
     * @throws \System\Http\Exception\\System\Http\Exceptions\HttpException
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }

    /**
     * Register aliases to container.
     */
    protected function registerAlias(): void
    {
        foreach ([
                     'request'       => [Request::class],
                     'view.instance' => [Templator::class],
                     'vite.gets'     => [Vite::class],
                     'config'        => [ConfigRepository::class],
                 ] as $abstract => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($abstract, $alias);
            }
        }
    }

    /**
     * Merge application provider and vendor package provider.
     *
     * @return AbstractServiceProvider[]
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }

    /**
     * Join the given paths together.
     *
     * @param  string  $basePath
     * @param  string ...$paths Additional paths.
     * @return string
     */
    protected function joinPaths(string $basePath, string ...$paths): string
    {
        $paths    = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $paths);
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $basePath . implode(DIRECTORY_SEPARATOR, $paths);
    }
}
