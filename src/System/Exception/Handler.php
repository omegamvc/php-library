<?php

declare(strict_types=1);

namespace System\Exception;

use System\Container\Container;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;
use System\Http\Exceptions\HttpResponseException;
use System\Http\Request\Request;
use System\Http\Response\Response;
use System\Http\Exceptions\HttpException;
use System\View\Templator;
use System\View\TemplatorFinder;
use Throwable;
use function array_map;
use function array_merge;

class Handler
{
    protected Container $app;

    /**
     * Do not report exception list.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReport = [];

    /**
     * Do not report exception list internal (framework).
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReportInternal = [
        HttpResponseException::class,
        HttpException::class,
    ];

    public function __construct(Container $application)
    {
        $this->app = $application;
    }

    /**
     * Render exception.
     *
     * @throws Throwable
     */
    public function render(Request $request, Throwable $th): Response
    {
        if ($request->isJson()) {
            return $this->handleJsonResponse($th);
        }

        if ($th instanceof HttpResponseException) {
            return $th->getResponse();
        }

        if ($th instanceof HttpException) {
            return $this->handleHttpException($th);
        }

        if (false === $this->isDebug()) {
            return $this->handleResponse($th);
        }

        throw $th;
    }

    /**
     * Report exception (usefully for logging).
     *
     * @param Throwable $th
     * @return void
     */
    public function report(Throwable $th): void
    {
        if ($this->dontReport($th)) {
            return;
        }
    }

    /**
     * Determinate if exception in list of do not report.
     */
    protected function dontReport(Throwable $th): bool
    {
        foreach (array_merge($this->dontReport, $this->dontReportInternal) as $report) {
            if ($th instanceof $report) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Throwable $th
     * @return Response
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    protected function handleJsonResponse(Throwable $th): Response
    {
        $response = new Response([
            'code'     => 500,
            'messages' => [
                'message'   => 'Internal Server Error',
            ]], 500);

        if ($th instanceof HttpException) {
            $response->setResponseCode($th->getStatusCode());
            $response->headers->add($th->getHeaders());
        }

        if ($this->isDebug()) {
            return $response->json([
                'code'     => $response->getStatusCode(),
                'messages' => [
                    'message'   => $th->getMessage(),
                    'exception' => $th::class,
                    'file'      => $th->getFile(),
                    'line'      => $th->getLine(),
                ],
            ]);
        }

        return $response->json();
    }

    /**
     * @param Throwable $th
     * @return Response
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    protected function handleResponse(Throwable $th): Response
    {
        return $this->isProduction()
            ? $this->handleHttpException(new HttpException(500, 'Internal Server Error'))
            : new Response($th->getMessage(), 500);
    }

    /**
     * @param HttpException $e
     * @return Response
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    protected function handleHttpException(HttpException $e): Response
    {
        $templator = $this->registerViewPath();
        $code      = $templator->viewExist((string) $e->getStatusCode())
            ? $e->getStatusCode()
            : 500;

        $this->app->set('view.instance', fn () => $templator);

        $response = view((string) $code);
        $response->setResponseCode($code);
        $response->headers->add($e->getHeaders());

        return $response;
    }

    /**
     * Register error view path.
     *
     * @return Templator
     * @throws DependencyResolutionException
     * @throws ServiceNotFoundException
     */
    public function registerViewPath(): Templator
    {
        $view_paths   = array_map(fn ($path): string => $path . 'pages/', view_paths());
        $view_paths[] = get_resources_path('views/');
        /** @var TemplatorFinder $finder */
        $finder = $this->app->make(TemplatorFinder::class);
        $finder->setPaths($view_paths);

        /** @var Templator $view */
        $view = $this->app->make('view.instance');
        $view->setFinder($finder);

        return $view;
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    private function isDebug(): bool
    {
        return $this->app->get('app.debug');
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    private function isProduction(): bool
    {
        return $this->app->get('environment') === 'prod';
    }
}
