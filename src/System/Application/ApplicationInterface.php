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
use System\Config\ConfigRepository;
use System\Container\ServiceProvider\AbstractServiceProvider;
use System\Http\Exceptions\HttpException;

/**
 * Application interface class.
 *
 * The `ApplicationInterface` defines the core contract for the application, encapsulating
 * various methods that manage the application's configuration, environment, bootstrapping
 * process, service provider registration, and termination. It includes utility methods to
 * handle paths, environment modes, and maintenance states, allowing developers to interact
 * with and control different aspects of the application lifecycle.
 *
 * @category  System
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.
 */
interface ApplicationInterface
{
    /** @var string Constants for DIRECTORY_SEPARATOR */
    public const string DS = DIRECTORY_SEPARATOR;

    /**
     * Load and set the application's configuration.
     *
     * @param ConfigRepository $config Holds the configuration repository instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function loadConfig(ConfigRepository $config): void;
    /**
     * Retrieve the default configuration for the application.
     *
     * @return array<string, mixed> The default configuration settings.
     */
    public function defaultConfig(): array;

    /**
     * Set the base path for the application.
     *
     * @param string $path Holds the base path of the application.
     * @return Application Return the current application instance.
     */
    public function setBasePath(string $path): Application;

    /**
     * Set the application path.
     *
     * @param string $path Holds the application directory path.
     * @return Application Return the current application instance.
     */
    public function setAppPath(string $path): Application;

    /**
     * Set the application model path.
     *
     * @param string $path Holds the model directory path.
     * @return Application Return the current Application instance.
     */
    public function setModelPath(string $path): Application;

    /**
     * Set the application base view path.
     *
     * @param string $path Holds the base view directory path.
     * @return Application Return the current Application instance.
     */
    public function setViewPath(string $path): Application;

    /**
     * Set the application views paths.
     *
     * @param string[] $paths Holds an array of view paths
     * @return Application Return the current Application instance.
     */
    public function setViewPaths(array $paths): Application;

    /**
     * Set the application controller path.
     *
     * @param string $path Holds the controller directory path.
     * @return Application Return the current Application instance.
     */
    public function setControllerPath(string $path): Application;

    /**
     * Set the application services path.
     *
     * @param string $path Holds the services directory path.
     * @return Application Return the current Application instance.
     */
    public function setServicesPath(string $path): Application;

    /**
     * Set the application component path.
     *
     * @param string $path Holds the component directory  path.
     * @return Application Return the current Application instance.
     */
    public function setComponentPath(string $path): Application;

    /**
     * Set the application command path.
     *
     * @param string $path Holds the command directory path.
     * @return Application Return the current Application instance.
     */
    public function setCommandPath(string $path): Application;

    /**
     * Set the application storage path.
     *
     * @param string $path Holds the storage directory path.
     * @return Application Return the current Application instance.
     */
    public function setStoragePath(string $path): Application;

    /**
     * Set the application cache path.
     *
     * @param string $path Holds the cache directory path.
     * @return Application Return the current Application instance.
     * @deprecated version 0.32 use compiled_view_path instead.
     */
    public function setCachePath(string $path): Application;

    /**
     * Set the application compiled view path.
     *
     * @param string $path Holds the compiled view directory path.
     * @return Application Return the current Application instance.
     */
    public function setCompiledViewPath(string $path): Application;

    /**
     * Set the application config path.
     *
     * @param string $path Holds the config directory path.
     * @return Application Return the current Application instance.
     */
    public function setConfigPath(string $path): Application;

    /**
     * Set the application middleware path.
     *
     * @param string $path Holds the middleware directory path.
     * @return Application Return the current Application instance.
     */
    public function setMiddlewarePath(string $path): Application;

    /**
     * Set the application service provider path.
     *
     * @param string $path Holds the provider directory path.
     * @return Application Return the current Application instance.
     */
    public function setProviderPath(string $path): Application;

    /**
     * Set the application migration path.
     *
     * @param string $path Holds the migration directory path.
     * @return Application Return the current Application instance.
     */
    public function setMigrationPath(string $path): Application;

    /**
     * Set the application seeder path.
     *
     * @param string $path Holds the seeders directory path.
     * @return Application Return the current Application instance.
     */
    public function setSeederPath(string $path): Application;

    /**
     * Set the application public path.
     *
     * @param string $path Holds the application public directory path.
     * @return Application Return the current Application instance.
     */
    public function setPublicPath(string $path): Application;

    /**
     * Retrieve the base path of the application.
     *
     * @return string Return the base path of the application.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getBasePath(): string;

    /**
     * Retrieve the application path.
     *
     * @return string Return the application directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getApplicationPath(): string;

    /**
     * Retrieve the application cache path.
     *
     * @return string Return the application cache directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getApplicationCachePath(): string;

    /**
     * Retrieve the model path.
     *
     * @return string Return the model directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getModelPath(): string;

    /**
     * Retrieve the base view path.
     *
     * @return string Return the base view directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getViewPath(): string;

    /**
     * Retrieve views paths.
     *
     * @return string[] Return an array of views directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getViewPaths(): array;

    /**
     * Retrieve the controller path.
     *
     * @return string Return the controller directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getControllerPath(): string;

    /**
     * Retrieve the services path.
     *
     * @return string Return the services directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getServicesPath(): string;

    /**
     * Retrieve the component path.
     *
     * @return string Return the component directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getComponentPath(): string;

    /**
     * Retrieve the command path.
     *
     * @return string Return the command directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getCommandPath(): string;

    /**
     * Retrieve the storage path.
     *
     * @return string Return the storage directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getStoragePath(): string;

    /**
     * Retrieve the cache path.
     *
     * @return string Return the cache director path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     * @deprecated version 0.32 use compiled_view_path instead.
     */
    public function getCachePath(): string;

    /**
     * Retrieve the compiled path.
     *
     * @return string Return the compiled view directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getCompiledViewPath(): string;

    /**
     * Retrieve the config path.
     *
     * @return string Return the config directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getConfigPath(): string;

    /**
     * Retrieve the middleware path.
     *
     * @return string Return the middleware directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getMiddlewarePath(): string;

    /**
     * Retrieve the provider path.
     *
     * @return string Return the provider directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getProviderPath(): string;

    /**
     * Retrieve the migration path.
     *
     * @return string Return the migration directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getMigrationPath(): string;

    /**
     * Retrieve the seeder path.
     *
     * @return string Return the seeder directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getSeederPath(): string;

    /**
     * Retrieve the public path.
     *
     * @return string Return the public directory path.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function getPublicPath(): string;

    /**
     * Retrieve the application's environment (e.g., production, development).
     *
     * @return string The environment of the application.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function getEnvironment(): string;

    /**
     * Check if debug mode is enabled for the application.
     *
     * @return bool True if debug mode is enabled, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function isDebugMode(): bool;

    /**
     * Check if the application is running in production mode.
     *
     * @return bool True if the application is in production mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function isProduction(): bool;

    /**
     * Check if the application is in development mode.
     *
     * @return bool True if the application is in development mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function isDev(): bool;

    /**
     * Check if the application has been booted.
     *
     * @return bool True if the application has been booted, false otherwise.
     */
    public function isBooted(): bool;

    /**
     * Check if the application has been bootstrapped.
     *
     * @return bool True if the application has been bootstrapped, false otherwise.
     */
    public function isBootstrapped(): bool;

    /**
     * Bootstrap the application with the given list of bootstrapped.
     *
     * @param array<int, class-string> $bootstrapped List of class names to bootstrap.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function bootstrapWith(array $bootstrapped): void;

    /**
     * Boot the service provider for the application.
     *
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function bootProvider(): void;

    /**
     * Register service providers for the application.
     *
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function registerProvider(): void;

    /**
     * Call the registered booting callbacks.
     *
     * @param callable[] $bootCallBacks List of boot callback functions.
     * @return void
     */
    public function callBootCallbacks(array $bootCallBacks): void;

    /**
     * Add a booting callback, to be called before booting is complete.
     *
     * @param callable $callback The callback to be added.
     * @return void
     */
    public function bootingCallback(callable $callback): void;

    /**
     * Add a booted callback, to be called after booting is complete.
     *
     * @param callable $callback The callback to be added.
     * @return void
     */
    public function bootedCallback(callable $callback): void;

    /**
     * Flush or reset the application (static).
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Register a service provider for the application.
     *
     * @param string $provider The class name of the service provider.
     * @return AbstractServiceProvider The registered service provider.
     */
    public function register(string $provider): AbstractServiceProvider;

    /**
     * Register a terminating callback.
     *
     * @param callable $terminateCallback The callback function to be called on termination.
     * @return Application Return the current application instance.
     */
    public function registerTerminate(callable $terminateCallback): Application;

    /**
     * Terminate the application.
     *
     * @return void
     */
    public function terminate(): void;

    /**
     * Check if the application is in maintenance mode.
     *
     * @return bool True if the application is in maintenance mode, false otherwise.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function isDownMaintenanceMode(): bool;

    /**
     * Get the configuration for the down maintenance mode.
     *
     * @return array<string, string|int|null> The configuration array for maintenance mode.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function getDownData(): array;

    /**
     * Abort the application with an HTTP exception.
     *
     * @param int $code The HTTP status code to abort with.
     * @param string $message The message to include with the HTTP exception.
     * @param array<string, string> $headers The headers to send with the exception.
     * @return void
     * @throws HttpException If the application aborts with an HTTP exception.
     */
    public function abort(int $code, string $message = '', array $headers = []): void;
}
