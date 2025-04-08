<?php

declare(strict_types=1);

namespace System\Http;

use Exception;
use System\Application\Application;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Http\Request\Request;
use System\Http\Response\Response;
use System\Integrate\Bootstrap\BootProviders;
use System\Integrate\Bootstrap\ConfigProviders;
use System\Integrate\Bootstrap\HandleExceptions;
use System\Integrate\Bootstrap\RegisterFacades;
use System\Integrate\Bootstrap\RegisterProviders;
use System\Integrate\Exceptions\Handler;
use System\Http\Middleware\MaintenanceMiddleware;
use System\Router\Router;
use Throwable;

use function array_merge;
use function array_reduce;
use function is_array;
use function is_string;
use function method_exists;

class Kernel
{
    /** @var array<int, class-string> Global middleware */
    protected array $middleware = [
        MaintenanceMiddleware::class,
    ];

    /** @var array<int, class-string> Middleware has register */
    protected array $middlewareUsed = [];

    /** @var array<int, class-string> Application bootstrap register. */
    protected array $serviceProvider = [
        ConfigProviders::class,
        HandleExceptions::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Set instance.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Handle http request.
     *
     * @param Request $request Incoming request
     * @return Response Response handle
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     * @throws Throwable
     */
    public function handle(Request $request): Response
    {
        $this->app->set('request', $request);

        try {
            $this->bootstrap();

            $dispatcher = $this->dispatcher($request);

            $pipeline = array_reduce(
                array_merge($this->middleware, $dispatcher['middleware']),
                fn ($next, $middleware) => fn ($req) => $this->app->call([$middleware, 'handle'], ['request' => $req, 'next' => $next]),
                fn ()                   => $this->responseType($dispatcher['callable'], $dispatcher['parameters'])
            );

            $response = $pipeline($request);
        } catch (Throwable $th) {
            $handler = $this->app->get(Handler::class);

            $handler->report($th);
            $response = $handler->render($request, $th);
        }

        return $response;
    }

    /**
     * Register serviceProvider.
     *
     * @return void
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->serviceProvider);
    }

    /**
     * Terminate Request and Response.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        $middleware = $this->dispatcherMiddleware($request) ?? [];
        foreach (array_merge($this->middleware, $middleware) as $middleware) {
            if (method_exists($middleware, 'terminate')) {
                $this->app->call([$middleware, 'terminate'], ['request' => $request, 'response' => $response]);
            }
        }

        $this->app->terminate();
    }

    /**
     * @param callable|string|array $callable   function to call
     * @param array $parameters parameters to use
     *
     * @throws Exception
     */
    private function responseType(array|callable|string $callable, array $parameters): Response
    {
        $content = $this->app->call($callable, $parameters);
        if ($content instanceof Response) {
            return $content;
        }

        if (is_string($content)) {
            return new Response($content);
        }

        if (is_array($content)) {
            return new Response($content);
        }

        throw new Exception('Content must return as response|string|array');
    }

    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    protected function dispatcher(Request $request): array
    {
        return ['callable' => new Response(), 'parameters' => [], 'middleware' => []];
    }

    /**
     * Dispatch to get request middleware.
     *
     * @param Request $request
     * @return array<int, class-string>|null
     */
    protected function dispatcherMiddleware(Request $request): ?array
    {
        return Router::current()['middleware'] ?? [];
    }
}
