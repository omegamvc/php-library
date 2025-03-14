<?php

declare(strict_types=1);

namespace System\Test\Integrate\Exceptions;

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Application\Application;
use System\Exceptions\Handler;
use System\Http\Exceptions\HttpException;
use System\Http\Kernel;
use System\Application\PackageManifest;
use System\Text\Str;
use System\View\Templator;
use System\View\TemplatorFinder;

final class HandlerTest extends TestCase
{
    private Application $app;
    private Kernel $kernel;
    private Handler $handler;

    /**
     * Mock Logger.
     *
     * @var string[]
     */
    public static array $logs = [];

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__);

        // overwrite PackageManifest has been set in Application before.
        $this->app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: dirname(__DIR__) . '/assets/app2/',
            application_cache_path: dirname(__DIR__) . '/assets/app2/bootstrap/cache/',
            vendor_path: '/app2/package/'
        ));

        $this->app->set(
            Kernel::class,
            fn () => new $this->kernel($this->app)
        );

        $this->app->set(
            \System\Exceptions\Handler::class,
            fn () => $this->handler
        );

        $this->kernel = new class($this->app) extends Kernel {
            protected function dispatcher(Request $request): array
            {
                throw new HttpException(429, 'Too Many Request');

                return [
                    'callable'   => fn () => new Response('ok', 200),
                    'parameters' => [],
                    'middleware' => [],
                ];
            }
        };

        $this->handler = new class($this->app) extends \System\Exceptions\Handler {
            public function render(Request $request, \Throwable $th): Response
            {
                // try to bypass test for json format
                if ($request->isJson()) {
                    return $this->handleJsonResponse($th);
                }

                if ($th instanceof HttpException) {
                    return new Response($th->getMessage(), $th->getStatusCode(), $th->getHeaders());
                }

                return parent::render($request, $th);
            }

            public function report(\Throwable $th): void
            {
                HandlerTest::$logs[] = $th->getMessage();
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        HandlerTest::$logs = [];
    }

    /** @test */
    public function itCanRenderException()
    {
        $kernel      = $this->app->make(Kernel::class);
        $response    = $kernel->handle(new Request('/test'));

        $this->assertEquals('Too Many Request', $response->getContent());
        $this->assertEquals(429, $response->getStatusCode());
    }

    /** @test */
    public function itCanReportException()
    {
        $kernel      = $this->app->make(Kernel::class);
        $kernel->handle(new Request('/test'));

        $this->assertEquals(['Too Many Request'], HandlerTest::$logs);
    }

    /** @test */
    public function itCanRenderJson()
    {
        $this->app->bootedCallback(function () {
            $this->app->set('app.debug', false);
        });

        $kernel      = $this->app->make(Kernel::class);
        $response    = $kernel->handle(new Request('/test', [], [], [], [], [], [
            'content-type' => 'application/json',
        ]));

        $this->assertEquals([
            'code'     => 500,
            'messages' => [
                'message'   => 'Internal Server Error',
            ],
        ], $response->getContent());
        $this->assertEquals(429, $response->getStatusCode());
    }

    /** @test */
    public function itCanRenderJsonForDebug()
    {
        $this->app->bootedCallback(function () {
            $this->app->set('app.debug', true);
        });

        $kernel      = $this->app->make(Kernel::class);
        $response    = $kernel->handle(new Request('/test', [], [], [], [], [], [
            'content-type' => 'application/json',
        ]));

        $content = $response->getContent();
        $this->assertEquals('Too Many Request', $content['messages']['message']);
        $this->assertEquals('System\Http\Exceptions\HttpException', $content['messages']['exception']);
        // skip meggase.file issue test with diferent platform
        $this->assertEquals(56, $content['messages']['line']);
        $this->assertEquals(429, $response->getStatusCode());
    }

    /** @test */
    public function itCanRenderHttpException()
    {
        $this->app->setViewPath('/assets/');
        $this->app->setViewPaths([
            '/assets/',
            '/assets/pages/',
        ]);
        $this->app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder(view_paths(), ['.php', '.template.php'])
        );

        $this->app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, __DIR__ . '/assets')
        );

        $this->app->set(
            'view.response',
            fn () => fn (string $view_path, array $portal = []): Response => new Response(
                $this->app->make(Templator::class)->render($view_path, $portal)
            )
        );

        $handler = $this->app->make(\System\Exceptions\Handler::class);

        $exception = new HttpException(429, 'Internal Error', null, []);
        $render    = (fn () => $this->{'handleHttpException'}($exception))->call($handler);

        $this->assertTrue(Str::contains($render->getContent(), '<h1>Too Many Request</h1>'));
    }
}
