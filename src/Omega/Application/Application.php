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

namespace Omega\Application;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Config\ConfigRepository;
use Omega\Container\Container;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Http\Exceptions\HttpException;
use Omega\Http\Request;
use Omega\Support\PackageManifest;
use Omega\Support\Path;
use Omega\Support\RequestMacroServiceProvider;
use Omega\Support\Singleton\SingletonTrait;
use Omega\Support\Vite;
use Omega\View\Templator;

use function array_map;
use function file_exists;
use function in_array;
use function rtrim;

use const DIRECTORY_SEPARATOR;

/**
 * The core Application class.
 *
 * This class serves as the main entry point of the application lifecycle.
 * It manages path resolution, service provider registration, container bindings,
 * boot and bootstrap sequences, as well as legacy constant definitions and alias handling.
 *
 * Acts as a wrapper around the dependency injection container,
 * and centralizes application state and configuration.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Application extends Container implements ApplicationInterface
{
    use SingletonTrait;

    /** @var string|null The base path of the application. */
    private ?string $basePath;

    /** @var AbstractServiceProvider[] List of all registered service providers. */
    private array $providers = [];

    /** @var AbstractServiceProvider[] List of service providers that have been booted. */
    private array $bootedProviders = [];

    /** @var AbstractServiceProvider[] List of service providers that have been loaded. */
    private array $loadedProviders = [];

    /** @var bool Indicates whether the application has completed the boot process. */
    private bool $isBooted = false;

    /** @var bool Indicates whether the application has completed the bootstrap process. */
    private bool $isBootstrapped = false;

    /** @var callable[] List of callbacks to run when the application is terminated. */
    private array $terminateCallback = [];

    /** @var callable[] List of callbacks to run before the application boots. */
    protected array $bootingCallbacks = [];

    /** @var callable[] List of callbacks to run after the application has booted. */
    protected array $bootedCallbacks = [];

    /**
     * Constructs the application instance.
     *
     * Initializes paths, base bindings, service providers, and container aliases.
     *
     * @param string|null $basePath The root directory of the application.
     */
    public function __construct(?string $basePath = null)
    {
        $this->basePath = rtrim($basePath ?? $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__, 5), '\/');

        Path::init($this->basePath);

        parent::__construct();

        // set base path
        $this->setBasePath($this->basePath);
        $this->setConfigPath(env('CONFIG_PATH', Path::getPath('app.config')));

        // base binding
        $this->setBaseBinding();

        // register base provider
        $this->register(RequestMacroServiceProvider::class);

        // register container alias
        $this->registerAlias();
    }

    /**
     * Registers the core bindings into the container.
     *
     * Sets the application instance and container aliases, and registers the package manifest.
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
     * Registers aliases for container services.
     *
     * Maps common string-based identifiers to their corresponding classes
     * within the container for easier access.
     *
     * @return void
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

    //  $configs = $this->app->get('config');
    /**
     * Merges core application providers with vendor-defined package providers.
     *
     * @return AbstractServiceProvider[] The combined list of providers.
     * @throws DependencyException If a service could not be resolved.
     * @throws NotFoundException If a class or value was not found in the container.
     */
    protected function getMergeProviders(): array
    {
        return [...$this->providers, ...$this->make(PackageManifest::class)->providers()];
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig(ConfigRepository $configs): void
    {
        $this->set('config', fn (): ConfigRepository => $configs);

        $this->set('environment', $configs['APP_ENV']);
        $this->set('app.debug', $configs['APP_DEBUG'] === 'true');
        $this->setViewPath($configs['VIEW_PATH']);
        $this->setViewPaths($configs['VIEW_PATHS']);
        $this->set('config.view.extensions', $configs['VIEW_EXTENSIONS']);
        $this->providers = $configs['PROVIDERS'];
    }

#region Application Setter
    /**
     * {@inheritdoc}
     */
    public function setAppPath(?string $path = null): self
    {
        $appPath = $path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

        $this->set('path.app', $appPath);

        return $this;
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
    public function setCachePath(?string $path = null): self
    {
        $this->set('path.cache', $path !== null
            ? Path::getPath($path)
            : Path::getPath('storage.app.cache'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandPath(?string $path = null): self
    {
        $this->set('path.command', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Commands'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponentPath(?string $path = null): self
    {
        $this->set('path.component', $path !== null
            ? Path::getPath($path)
            : Path::getPath('resources.components'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompiledViewPath(?string $path = null): self
    {
        $this->set('path.compiled_view_path', $path !== null
            ? Path::getPath($path)
            : Path::getPath('resources.components'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerPath(?string $path = null): self
    {
        $this->set('path.controller', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Controllers'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigPath(?string $path = null): self
    {
        $this->set('path.config', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.config'));

        $configPath = $this->basePath . $path;
        $this->set('path.config', $configPath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMiddlewarePath(?string $path = null): self
    {
        $this->set('path.middleware', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Middlewares'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMigrationPath(?string $path = null): self
    {
        $this->set('path.migration', $path !== null
            ? Path::getPath($path)
            : Path::getPath('database.migration'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setModelPath(?string $path = null): self
    {
        $this->set('path.model', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Models'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderPath(?string $path = null): self
    {
        $this->set('path.provider', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Providers'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicPath(?string $path = null): self
    {
        $this->set('path.public', $path !== null
            ? Path::getPath($path)
            : Path::getPath('public'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSeederPath(?string $path = null): self
    {
        $this->set('path.seeder', $path !== null
            ? Path::getPath($path)
            : Path::getPath('database.seeders'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServicesPath(?string $path = null): self
    {
        $this->set('path.services', $path !== null
            ? Path::getPath($path)
            : Path::getPath('app.Services'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoragePath(?string $path = null): self
    {
        $this->set('path.storage', $path !== null
            ? Path::getPath($path)
            : Path::getPath('storage'));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewPath(string $path): self
    {
        $viewPath = $this->basePath . $path;
        $this->set('path.view', $viewPath);

        return $this;
    }

    /**public function setViewPath(?string $path = null): self
    {
        $this->set('path.view', $path !== null
            ? Path::getPath($path)
            : Path::getPath('resources.views'));

        return $this;
    }*/

    /**
     * {@inheritdoc}
     */
    public function setViewPaths(array $paths): self
    {
        $viewPaths = array_map(fn ($path) => $this->basePath . $path, $paths);
        $this->set('paths.view', $viewPaths);

        return $this;
    }

    /**public function setViewPaths(?array $paths = null): self
    {
        if (is_null($paths)) {
            $paths = [Path::getPath('resources.views')];
        }

        $viewPaths = array_map(fn ($path) => / **$this->basePath .* / $path, $paths);
        var_dump($viewPaths);
        $this->set('paths.view', $viewPaths);

        return $this;
    }*/

#endregion

#region Application Getter
    /**
     * {@inheritdoc}
     */
    public function getAppPath(): string
    {
        return $this->get('path.app');
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationCachePath(): string
    {
        return rtrim($this->getBasePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
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
    public function getCachePath(?string $path = null): string
    {
        if (!$this->has('path.cache')) {
            $this->setCachePath($path);
        }

        return $this->get('path.cache');
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandPath(?string $path = null): string
    {
        if (!$this->has('path.command')) {
            $this->setCommandPath($path);
        }

        return $this->get('path.command');
    }

    /**
     * {@inheritdoc}
     */
    public function getComponentPath(?string $path = null): string
    {
        if (!$this->has('path.component')) {
            $this->setComponentPath($path);
        }

        return $this->get('path.component');
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledViewPath(?string $path = null): string
    {
        if (!$this->has('path.compiled_view_path')) {
            $this->setCompiledViewPath($path);
        }

        return $this->get('path.compiled_view_path');
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerPath(?string $path = null): string
    {
        if (!$this->has('path.controller')) {
            $this->setControllerPath($path);
        }

        return $this->get('path.controller');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(?string $path = null): string
    {
        if (!$this->has('path.config')) {
            $this->setConfigPath($path);
        }

        return $this->get('path.config');
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
    public function getMiddlewarePath(?string $path = null): string
    {
        if (!$this->has('path.middleware')) {
            $this->setMiddlewarePath($path);
        }

        return $this->get('path.middleware');
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationPath(?string $path = null): string
    {
        if (!$this->has('path.migration')) {
            $this->setMigrationPath($path);
        }

        return $this->get('path.migration');
    }

    /**
     * {@inheritdoc}
     */
    public function getModelPath(?string $path = null): string
    {
        if (!$this->has('path.model')) {
            $this->setModelPath($path);
        }

        return $this->get('path.model');
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderPath(?string $path = null): string
    {
        if (!$this->has('path.provider')) {
            $this->setProviderPath($path);
        }

        return $this->get('path.provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(?string $path = null): string
    {
        if (!$this->has('path.public')) {
            $this->setPublicPath($path);
        }

        return $this->get('path.public');
    }

    /**
     * {@inheritdoc}
     */
    public function getSeederPath(?string $path = null): string
    {
        if (!$this->has('path.seeder')) {
            $this->setSeederPath($path);
        }

        return $this->get('path.seeder');
    }

    /**
     * {@inheritdoc}
     */
    public function getServicesPath(?string $path = null): string
    {
        if (!$this->has('path.services')) {
            $this->setServicesPath($path);
        }

        return $this->get('path.services');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(?string $path = null): string
    {
        if (!$this->has('path.storage')) {
            $this->setStoragePath($path);
        }

        return $this->get('path.storage');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(?string $path = null): string
    {
        /**if (!$this->has('path.view')) {
            $this->setViewPath($path);
        }*/

        return $this->get('path.view');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPaths(): array
    {
        /*if (!$this->has('paths.view')) {
            $this->setViewPaths($paths);
        }*/

        return $this->get('paths.view');
    }
#endregion

#region Application conditional
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
    public function isDev(): bool
    {
        return $this->getEnvironment() === 'dev';
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
#endregion

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $providers): void
    {
        $this->isBootstrapped = true;

        foreach ($providers as $provider) {
            $this->make($provider)->bootstrap($this);
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
        $this->loadedProviders   = [];
        $this->bootedProviders   = [];
        $this->terminateCallback = [];
        $this->bootingCallbacks  = [];
        $this->bootedCallbacks   = [];

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
        return file_exists($this->getStoragePath() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
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
     * {@inheritdoc}
     */
    public function abort(int $code, string $message = '', array $headers = []): void
    {
        throw new HttpException($code, $message, null, $headers);
    }
}
