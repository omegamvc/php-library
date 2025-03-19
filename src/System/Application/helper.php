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
use System\Collection\CollectionImmutable;
use System\Http\RedirectResponse;
use System\Application\Exceptions\ApplicationNotAvailableException;
use System\Http\Response;
use System\Router\Router;
use System\Support\Vite;

use function array_key_exists;
use function count;
use function function_exists;
use function preg_replace_callback;

/**
 * Helper Functions for Omega Framework
 *
 * This file provides a collection of helper functions for the Omega framework.
 * These functions facilitate access to application paths, configurations, views,
 * and other essential components, improving developer experience and efficiency.
 *
 * Key Features:
 * - Retrieve various application paths (e.g., models, views, controllers, storage, config).
 * - Access application environment details (e.g., check if in production or development mode).
 * - Manage views using a custom template engine.
 * - Handle redirects and route-based navigation.
 * - Simplify access to application configurations and dependency injection container.
 * - Integrate with the Vite asset management system.
 * - Abort execution with HTTP exceptions when needed.
 *
 * Each function ensures compatibility with the application container and follows a
 * structured error-handling approach.
 *
 * @category  System
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
if (!function_exists('app_path')) {
    /**
     * Get full application path, base on config file.
     *
     * @param string $folder Special path name
     * @return string Application path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function app_path(string $folder): string
    {
        return app()->getApplicationPath() . DIRECTORY_SEPARATOR . $folder;
    }
}

if (!function_exists('model_path')) {
    /**
     * Get application model path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Model path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function model_path(string $path = ''): string
    {
        return app()->getModelPath() . $path;
    }
}

if (!function_exists('view_path')) {
    /**
     * Get application base view path, use for get located view frame work..
     * Remember since 0.32 view path is not single string (array of string).
     * This also include in `viewPaths()`.
     *
     * @param string $path Add string end of path
     * @return string View path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function view_path(string $path = ''): string
    {
        return app()->getViewPath() . $path;
    }
}

if (!function_exists('view_paths')) {
    /**
     * Get application view paths, base on config file.
     *
     * @return string[] View path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function view_paths(): array
    {
        return app()->getViewPaths();
    }
}

if (!function_exists('controllers_path')) {
    /**
     * Get application controllers path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Controller path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function controllers_path(string $path = ''): string
    {
        return app()->getControllerPath() . $path;
    }
}

if (!function_exists('services_path')) {
    /**
     * Get application services path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Service path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function services_path(string $path = ''): string
    {
        return app()->getServicesPath() . $path;
    }
}

if (!function_exists('component_path')) {
    /**
     * Get application component path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Component path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function component_path(string $path = ''): string
    {
        return app()->getComponentPath() . $path;
    }
}

if (!function_exists('commands_path')) {
    /**
     * Get application commands path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Command path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function commands_path(string $path = ''): string
    {
        return app()->getCommandPath() . $path;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get application storage path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string storage path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function storage_path(string $path = ''): string
    {
        return app()->getStoragePath() . $path;
    }
}

if (!function_exists('compiled_view_path')) {
    /**
     * Get application compiled path., base on config file.
     *
     * @return string Cache path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function compiled_view_path(): string
    {
        return app()->getCompiledViewPath();
    }
}

if (!function_exists('config_path')) {
    /**
     * Get application config path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Config path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function config_path(string $path = ''): string
    {
        return app()->getConfigPath() . $path;
    }
}

if (!function_exists('middleware_path')) {
    /**
     * Get application middleware path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Middleware path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function middleware_path(string $path = ''): string
    {
        return app()->getMiddlewarePath() . $path;
    }
}

if (!function_exists('provider_path')) {
    /**
     * Get application provider path, base on config file.
     *
     * @param string $path
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    function provider_path(string $path = ''): string
    {
        return app()->getProviderPath() . $path;
    }
}

if (!function_exists('migration_path')) {
    /**
     * Get application migration path, base on config file.
     *
     * @param string $path
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    function migration_path(string $path = ''): string
    {
        return app()->getMigrationPath() . $path;
    }
}

if (!function_exists('seeder_path')) {
    /**
     * Get application seeder path, base on config file.
     *
     * @param string $path
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    function seeder_path(string $path = ''): string
    {
        return app()->getSeederPath() . $path;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get base path.
     *
     * @param string $insertPath Insert string in end of path
     * @return string Base path folder
     * @throws DependencyException
     * @throws NotFoundException
     */
    function base_path(string $insertPath = ''): string
    {
        return app()->getBasePath() . $insertPath;
    }
}

if (!function_exists('app_env')) {
    /**
     * Check application environment mode.
     *
     * @return string Application environment mode
     * @throws DependencyException
     * @throws NotFoundException
     */
    function app_env(): string
    {
        return app()->getEnvironment();
    }
}

if (!function_exists('is_production')) {
    /**
     * Check application production mode.
     *
     * @return bool True if in production mode
     * @throws DependencyException
     * @throws NotFoundException
     */
    function is_production(): bool
    {
        return app()->isProduction();
    }
}

if (!function_exists('is_dev')) {
    /**
     * Check application development mode.
     *
     * @return bool True if in dev mode
     * @throws DependencyException
     * @throws NotFoundException
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get the Application container.
     *
     * @return Application The singleton instance of Application.
     * @throws ApplicationNotAvailableException If the application instance is not available.
     */
    function app(): Application
    {
        return Application::getInstance() ?? throw new ApplicationNotAvailableException();
    }
}

if (!function_exists('config')) {
    /**
     * Get Application Configuration.
     *
     * @return CollectionImmutable<string, mixed>
     * @throws DependencyException
     * @throws NotFoundException
     */
    function config(): CollectionImmutable
    {
        return new CollectionImmutable(app()->get('config'));
    }
}

if (!function_exists('view')) {
    /**
     * Render with costume template engine, wrap in `Route\Controller`.
     *
     * @param string               $viewPath
     * @param array<string, mixed> $data
     * @param array<string, mixed> $option
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    function view(string $viewPath, array $data = [], array $option = []): Response
    {
        $view       = app()->get('view.response');
        $statusCode = $option['status'] ?? 200;
        $headers    = $option['header'] ?? [];

        return $view($viewPath, $data)
            ->setResponeCode($statusCode)
            ->setHeaders($headers);
    }
}

if (!function_exists('vite')) {
    /**
     * Get resource using entry point(s).
     *
     * @param string $entryPoints
     * @return array<string, string>|string
     * @throws DependencyException
     * @throws NotFoundException
     */
    function vite(...$entryPoints): array|string
    {
        /** @var Vite $vite */
        $vite = app()->get('vite.gets');

        return $vite(...$entryPoints);
    }
}

if (!function_exists('redirect_route')) {
    /**
     * Redirect to another route.
     *
     * @param string   $routeName
     * @param string[] $parameter Dynamic parameter to fill with url expression
     * @return RedirectResponse
     * @throws Exception
     */
    function redirect_route(string $routeName, array $parameter = []): RedirectResponse
    {
        $route      = Router::redirect($routeName);
        $valueIndex = 0;
        $url        = preg_replace_callback(
            "/\(:\w+\)/",
            function ($matches) use ($parameter, &$valueIndex) {
                if (!array_key_exists($matches[0], Router::$patterns)) {
                    throw new Exception('parameter not matches with any pattern.');
                }

                if ($valueIndex < count($parameter)) {
                    $value = $parameter[$valueIndex];
                    $valueIndex++;

                    return $value;
                }

                return '';
            },
            $route['uri']
        );

        return new RedirectResponse($url);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to Url.
     */
    function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort application to http exception.
     *
     * @param int                   $code
     * @param string                $message
     * @param array<string, string> $headers
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        app()->abort($code, $message, $headers);
    }
}
