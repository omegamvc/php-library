<?php

declare(strict_types=1);

use System\Application\Application;
use System\Collection\CollectionImmutable;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Http\Response\RedirectResponse;
use System\Http\Response\Response;
use System\Integrate\Exceptions\ApplicationNotAvailable;
use System\Integrate\Vite;
use System\Router\Router;
use System\Support\Env;

if (!function_exists('get_app_path')) {
    /**
     * Get application commands path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Command path folder
     */
    function get_app_path(string $path = ''): string
    {
        return app()->getAppPath($path);
    }
}

if (!function_exists('get_base_path')) {
    /**
     * Get base path.
     *
     * @param string $path Insert string in end of path
     * @return string Base path folder
     */
    function get_base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('get_bin_path')) {
    function get_bin_path(string $path = ''): string
    {
        return app()->getBinPath($path);
    }
}

if (!function_exists('git_application_cache_path')) {
    function get_application_cache_path(string $path = ''): string
    {
        return app()->getApplicationCachePath($path);
    }
}

if (!function_exists('get_config_path')) {
    /**
     * Get application config path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Config path folder
     */
    function get_config_path(string $path = ''): string
    {
        return app()->getConfigPath($path);
    }
}

if (!function_exists('get_database_path')) {
    /**
     * Get application migration path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Migration path folder
     */
    function get_database_path(string $path = ''): string
    {
        return app()->getDatabasePath($path);
    }
}

if (!function_exists('get_public_path')) {
    /**
     * Get application migration path, base on config file.
     *
     * @param string $path Add string end of path
     * @return string Migration path folder
     */
    function get_public_path(string $path = ''): string
    {
        return app()->getPublicPath($path);
    }
}

if (!function_exists('get_resources_path')) {
    function get_resources_path(string $path = ''): string
    {
        return app()->getResourcesPath($path);
    }
}

if (!function_exists('get_routes_path')) {
    function get_routes_path(string $path = ''): string
    {
        return app()->getRoutesPath($path);
    }
}

if (!function_exists('get_storage_path')) {
    function get_storage_path(string $path = ''): string
    {
        return app()->getStoragePath($path);
    }
}

if (!function_exists('get_test_path')) {
    function get_tests_path(string $path = ''): string
    {
        return app()->getTestPath($path);
    }
}

if (!function_exists('get_vendor_path')) {
    function get_vendor_path(string $path = ''): string
    {
        return app()->getVendorPath($path);
    }
}

if (!function_exists('view_paths')) {
    /**
     * Get application view paths, base on config file.
     *
     * @return string[] View path folder
     */
    function view_paths(): array
    {
        return app()->getViewPaths();
    }
}

if (!function_exists('app_env')) {
    /**
     * Check application environment mode.
     *
     * @return string Application environment mode
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
     */
    function is_dev(): bool
    {
        return app()->isDev();
    }
}

if (!function_exists('app')) {
    /**
     * Get Application container.
     */
    function app(): Application
    {
        $app = Application::getInstance();
        if (null === $app) {
            throw new ApplicationNotAvailable();
        }

        return $app;
    }
}

if (!function_exists('config')) {
    /**
     * Get Application Configuration.
     *
     * @return CollectionImmutable<string, mixed>
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
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
     * @return \System\Http\Response\Response
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    function view(string $viewPath, array $data = [], array $option = []): Response
    {
        $view = app()->get('view.response');
        $status_code = $option['status'] ?? 200;
        $headers = $option['header'] ?? [];

        return $view($viewPath, $data)
            ->setResponseCode($status_code)
            ->setHeaders($headers);
    }
}

if (!function_exists('vite')) {
    /**
     * Get resource using entry point(s).
     *
     * @param string $entryPoints
     * @return array<string, string>|string
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    function vite(...$entryPoints): array|string
    {
        /** @var Vite $view */
        $vite = app()->get('vite.gets');

        return $vite(...$entryPoints);
    }
}

if (!function_exists('redirect_route')) {
    /**
     * Redirect to another route.
     *
     * @param string[] $parameter Dynamic parameter to fill with url expression
     * @throws Exception
     */
    function redirect_route(string $route_name, array $parameter = []): RedirectResponse
    {
        $route = Router::redirect($route_name);
        $valueIndex = 0;
        $url = preg_replace_callback(
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
     * @param array<string, string> $headers
     */
    function abort(int $code, string $message = '', array $headers = []): void
    {
        app()->abort($code, $message, $headers);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
