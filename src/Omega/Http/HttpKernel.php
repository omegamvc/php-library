<?php

declare(strict_types=1);

namespace Omega\Http;

use Omega\Http\Middleware\MaintenanceMiddleware;
use Omega\Integrate\Application;
use Omega\Integrate\Bootstrap\BootProviders;
use Omega\Integrate\Bootstrap\ConfigProviders;
use Omega\Integrate\Bootstrap\HandleExceptions;
use Omega\Integrate\Bootstrap\RegisterFacades;
use Omega\Integrate\Bootstrap\RegisterProviders;
use Omega\Integrate\Exceptions\ExceptionHandler;
use Omega\Router\Router;

class HttpKernel
{
    /**
     * Application Container.
     */
    protected Application $app;

    /** @var array<int, class-string> Global middleware */
    protected $middleware = [
        MaintenanceMiddleware::class,
    ];

    /** @var array<int, class-string> Middleware has register */
    protected $middleware_used = [];

    /** @var array<int, class-string> Apllication bootstrap register. */
    protected array $bootstrappers = [
        ConfigProviders::class,
        HandleExceptions::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Set instance.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle http request.
     *
     * @param Request $request Incoming request
     *
     * @return Response Respone handle
     */
    public function handle(Request $request)
    {
        $this->app->set('request', $request);

        try {
            $this->bootstrap();

            $dispatcher = $this->dispatcher($request);

            $pipeline = array_reduce(
                array_merge($this->middleware, $dispatcher['middleware']),
                fn ($next, $middleware) => fn ($req) => $this->app->call([$middleware, 'handle'], ['request' => $req, 'next' => $next]),
                fn ()                   => $this->responesType($dispatcher['callable'], $dispatcher['parameters'])
            );

            $response = $pipeline($request);
        } catch (\Throwable $th) {
            $handler = $this->app->get(ExceptionHandler::class);

            $handler->report($th);
            $response = $handler->render($request, $th);
        }

        return $response;
    }

    /**
     * Register bootstraper application.
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * Terminate Requesr and Response.
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
     * @param callable|mixed[]|string $callable   function to call
     * @param mixed[]                 $parameters parameters to use
     *
     * @throws \Exception
     */
    private function responesType($callable, $parameters): Response
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

        throw new \Exception('Content must return as respone|string|array');
    }

    /**
     * @return array<string, mixed>
     */
    protected function dispatcher(Request $request): array
    {
        return ['callable' => new Response(), 'parameters' => [], 'middleware' => []];
    }

    /**
     * Dispatch to get requets middleware.
     *
     * @return array<int, class-string>|null
     */
    protected function dispatcherMiddleware(Request $request)
    {
        return Router::current()['middleware'] ?? [];
    }
}
