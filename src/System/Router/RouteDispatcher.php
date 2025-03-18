<?php

declare(strict_types=1);

namespace System\Router;

use System\Http\Request;

final class RouteDispatcher
{
    // dispatch
    /** @var Request */
    private Request $request;

    /** @var Route[] */
    private array $routes = [];

    // callback ------------------
    /** @var callable */
    private mixed $found;

    /** @var ?callable(string): mixed */
    private mixed $notFound;

    /** @var ?callable(string, string): mixed */
    private mixed $methodNotAllowed;

    // setup --------------------
    private string $basePath           = '';

    private bool $caseMatters          = false;

    private bool $trailingSlashMatters = false;

    private bool $multiMatch           = false;

    /** @var array<string, mixed> */
    private array $trigger;

    /** @var Route */
    private Route $current;

    /**
     * @param Request $request Incoming request
     * @param Route[] $routes  Array of route
     */
    public function __construct(Request $request, array $routes)
    {
        $this->request = $request;
        $this->routes  = $routes;
    }

    /**
     * Create new costruct using uri and method.
     *
     * @param string  $uri    Ulr
     * @param string  $method Method
     * @param Route[] $routes Array of route
     */
    public static function dispatchFrom(string $uri, string $method, $routes): self
    {
        $create_request = new Request($uri, [], [], [], [], [], [], $method);

        return new static($create_request, $routes);
    }

    // setter -----------------------------------

    /**
     * Setup Base Path.
     *
     * @param string $base_path Base Path
     *
     * @return self
     */
    public function basePath(string $base_path)
    {
        $this->basePath = $base_path;

        return $this;
    }

    /**
     * Cese sensitive metters.
     *
     * @param bool $case_matters Cese sensitive metters
     *
     * @return self
     */
    public function caseMatters(bool $case_matters)
    {
        $this->caseMatters = $case_matters;

        return $this;
    }

    /**
     * Trailing slash matters.
     *
     * @param bool $trailling_slash_metters Trailing slash matters
     *
     * @return self
     */
    public function trailingSlashMatters(bool $trailling_slash_metters)
    {
        $this->trailingSlashMatters = $trailling_slash_metters;

        return $this;
    }

    /**
     * Return Multy route.
     *
     * @param bool $multimath Return Multy route
     *
     * @return self
     */
    public function multimatch(bool $multimath)
    {
        $this->multiMatch = $multimath;

        return $this;
    }

    // getter -----------------------------------

    /**
     * Get current router after dispatch.
     *
     * @return Route
     */
    public function current()
    {
        return $this->current;
    }

    // method -----------------------------------

    /**
     * Setup action and dispatch route.
     *
     * @return array<string, mixed> trigger arction ['callable' => callable, 'param' => param]
     */
    public function run(callable $found, callable $not_found, callable $method_not_allowed)
    {
        $this->found              = $found;
        $this->notFound          = $not_found;
        $this->methodNotAllowed = $method_not_allowed;

        $this->dispatch($this->basePath, $this->caseMatters, $this->trailingSlashMatters, $this->multiMatch);

        return $this->trigger;
    }

    /**
     * Catch action from callable (found, not_found, method_not_allowed).
     *
     * @param callable                   $callable   Callback
     * @param array<int, mixed|string[]> $params     Callaback params
     * @param class-string[]             $middleware Array of middleware class-name
     */
    private function trigger(callable $callable, $params, $middleware = []): void
    {
        $this->trigger = [
            'callable'      => $callable,
            'params'        => $params,
            'middleware'    => $middleware,
        ];
    }

    /**
     * Dispatch routes and setup trigger.
     *
     * @param string $basePath             Base Path
     * @param bool   $caseMatters          Case sensitive matters
     * @param bool   $trailingSlashMatters Trailing slash matters
     * @param bool   $multiMatch           Return Multi route
     */
    private function dispatch(
        string $basePath = '',
        bool $caseMatters = false,
        bool $trailingSlashMatters = false,
        bool $multiMatch = false
    ): void {
        // The basePath never needs a trailing slash
        // Because the trailing slash will be added using the route expressions
        $basePath = rtrim($basePath, '/');

        // Parse current URL
        $parsed_url = parse_url($this->request->getUrl());

        $path = '/';

        // If there is a path available
        if (isset($parsed_url['path'])) {
            // If the trailing slash matters
            if ($trailingSlashMatters) {
                $path = $parsed_url['path'];
            } else {
                // If the path is not equal to the base path (including a trailing slash)
                if ($basePath . '/' != $parsed_url['path']) {
                    // Cut the trailing slash away because it does not matters
                    $path = rtrim($parsed_url['path'], '/');
                } else {
                    $path = $parsed_url['path'];
                }
            }
        }

        // Get current request method
        $method = $this->request->getMethod();

        $path_match_found  = false;
        $route_match_found = false;

        foreach ($this->routes as $route) {
            // If the method matches check the path

            // Add basepath to matching string
            if ($basePath != '' && $basePath != '/') {
                $route['expression'] = '(' . $basePath . ')' . $route['expression'];
            }

            // Add 'find string start' automatically
            $route['expression'] = '^' . $route['expression'];

            // Add 'find string end' automatically
            $route['expression'] .= '$';

            // Check path match
            if (preg_match('#' . $route['expression'] . '#' . ($caseMatters ? '' : 'i') . 'u', $path, $matches)) {
                $path_match_found = true;

                // Cast allowed method to array if it's not one already, then run through all methods
                foreach ((array) $route['method'] as $allowedMethod) {
                    // Check method match
                    if (strtolower($method) == strtolower($allowedMethod)) {
                        array_shift($matches); // Always remove first element. This contains the whole string

                        if ($basePath != '' && $basePath != '/') {
                            array_shift($matches); // Remove basepath
                        }

                        // execute request
                        $this->trigger($this->found, [$route['function'], $matches], $route['middleware'] ?? []);
                        $this->current = $route;

                        $route_match_found = true;

                        // Do not check other routes
                        break;
                    }
                }
            }

            // Break the loop if the first found route is a match
            if ($route_match_found && !$multiMatch) {
                break;
            }
        }

        // No matching route was found
        if (!$route_match_found) {
            // But a matching path exists
            if ($path_match_found) {
                if ($this->methodNotAllowed) {
                    $this->trigger($this->methodNotAllowed, [$path, $method]);
                }
            } else {
                if ($this->notFound) {
                    $this->trigger($this->notFound, [$path]);
                }
            }
        }
    }
}
