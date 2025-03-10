<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use System\Http\Request;
use System\Http\Response;
use System\Application\Application;
use System\Integrate\Http\Kernel;

final class KernelTerminateTest extends TestCase
{
    private $app;
    private $kernel;

    protected function setUp(): void
    {
        $this->app = new Application('/');

        $this->app->set(
            Kernel::class,
            fn () => new $this->kernel($this->app)
        );

        $this->kernel = new class($this->app) extends Kernel {
            public function handle(Request $request)
            {
                return new Response('ok');
            }

            protected function dispatcherMiddleware(Request $request)
            {
                return [TestKernelTerminate::class];
            }
        };
    }

    protected function tearDown(): void
    {
        $this->app->flush();
        $this->kernel = null;
    }

    /** @test */
    public function itCanTerminate()
    {
        $kernel      = $this->app->make(Kernel::class);
        $response    = $kernel->handle(
            $request = new Request('/test')
        );

        $this->app->registerTerminate(static function () {
            echo 'terminated.';
        });

        ob_start();
        $kernel->terminate($request, $response);
        $out = ob_get_clean();

        $this->assertEquals('/testterminated.', $out);
    }
}

class TestKernelTerminate
{
    public function terminate(Request $request, Response $respone)
    {
        echo $request->getUrl();
        echo $respone->getContent();
    }
}
