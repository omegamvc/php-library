<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use PHPUnit\Framework\TestCase;
use Omega\Integrate\Application;
use Omega\Config\ConfigRepository;
use Omega\Console\Commands\ViewCommand;
use Omega\View\Templator;
use Omega\View\TemplatorFinder;

class ViewCommandsTest extends TestCase
{
    /**
     * Test it can compile from templator files.
     *
     * @return void
     */
    public function testItCanCompileFromTemplatorFiles(): void
    {
        $app = new Application(__DIR__);

        $app->setCachePath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view_cache' . DIRECTORY_SEPARATOR);
        $app->setViewPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);

        $app->set(
            TemplatorFinder::class,
            fn () => new TemplatorFinder([view_path()], ['.php', ''])
        );

        $app->set(
            'view.instance',
            fn (TemplatorFinder $finder) => new Templator($finder, cache_path())
        );

        $view_command = new ViewCommand(['php', 'omega', 'view:cache'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $view_command->cache($app->make(Templator::class));
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertFileExists(cache_path() . md5('test.php') . '.php');
    }

    /**
     * Test it can clear compiled view file.
     *
     * @return void
     */
    public function testItCanClearCompiledViewFile(): void
    {
        // Tests\Integrate\Commands\assets\view_cache
        $app = new Application('');
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        $app->setCachePath(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'view_cache' . DIRECTORY_SEPARATOR);

        file_put_contents(cache_path() . 'test01.php', '');
        file_put_contents(cache_path() . 'test02.php', '');
        $view_command = new ViewCommand(['php', 'omega', 'view:clear'], [
            'prefix' => '*.php',
        ]);
        ob_start();
        $exit = $view_command->clear();
        ob_get_clean();
        $this->assertEquals(0, $exit);
    }
}
