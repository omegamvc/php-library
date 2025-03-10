<?php

declare(strict_types=1);

namespace System\Test\Integrate\Testing;

use System\Application\Application;
use System\Integrate\Http\Kernel;
use System\Integrate\Testing\TestCase;

final class TestCaseTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'RegisterProvidersTest.php';
        $this->app = new Application(dirname(__DIR__) . '/assets/app2');
        $this->app->set(Kernel::class, fn () => new Kernel($this->app));

        parent::setUp();
    }

    public function testTestRunSmoothly(): void
    {
        $this->assertTrue(true);
    }
}
