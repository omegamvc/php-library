<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\MakeCommand;

final class MakeCommandsTest extends CommandTest
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!file_exists($command_config = __DIR__ . '/assets/command.php')) {
            file_put_contents($command_config,
                '<?php return array_merge(
                    // more command here
                );'
            );
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($command_config = __DIR__ . '/assets/command.php')) {
            unlink($command_config);
        }

        if (file_exists($assetController = __DIR__ . '/assets/IndexController.php')) {
            unlink($assetController);
        }

        if (file_exists($view = __DIR__ . '/assets/welcome.template.php')) {
            unlink($view);
        }

        if (file_exists($service = __DIR__ . '/assets/ApplicationService.php')) {
            unlink($service);
        }

        if (file_exists($command = __DIR__ . '/assets/CacheCommand.php')) {
            unlink($command);
        }

        $migration = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR;
        array_map('unlink', glob("{$migration}/*.php"));
    }

    /**
     * Test it can call make command controller with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandControllerWithSuccess(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:controller Index'));
        ob_start();
        $exit = $makeCommand->make_controller();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/IndexController.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class IndexController extends Controller', $class);
        $this->assertContain('public function index(): Response', $class);
    }

    /**
     * Test it can call make command controller with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandControllerWithFails(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:controller Asset'));
        ob_start();
        $exit = $makeCommand->make_controller();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command view with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandViewWithSuccess(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:view welcome'));
        ob_start();
        $exit = $makeCommand->make_view();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/welcome.template.php';
        $this->assertTrue(file_exists($file));

        $view = file_get_contents($file);
        $this->assertContain('<title>Document</title>', $view);
    }

    /**
     * Test it can call make command view with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandViewWithFails(): void
    {
        $makeCommand = new MakeCommand($this->argv('omega make:view asset'));
        ob_start();
        $exit = $makeCommand->make_view();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command service with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandServiceWithSuccess(): void
    {
        $make_service = new MakeCommand($this->argv('omega make:service Application'));
        ob_start();
        $exit = $make_service->make_services();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/ApplicationService.php';
        $this->assertTrue(file_exists($file));

        $service = file_get_contents($file);
        $this->assertContain('class ApplicationService extends Service', $service);
    }

    /**
     * Test it can call make command service with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandServiceWithFails(): void
    {
        $make_service = new MakeCommand($this->argv('omega make:service Asset'));
        ob_start();
        $exit = $make_service->make_services();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command a commands with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandACommandsWithSuccess(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:command Cache'));
        ob_start();
        $exit = $make_command->make_command();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/CacheCommand.php';
        $this->assertTrue(file_exists($file));

        $command = file_get_contents($file);
        $this->assertContain('class CacheCommand extends Command', $command);
    }

    /**
     * Test it can call make command a commands with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandACommandsWithFails(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:command Asset'));
        ob_start();
        $exit = $make_command->make_command();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command migration with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandMigrationWithSuccess(): void
    {
        $make_command = new MakeCommand($this->argv('omega make:migration user'));
        ob_start();
        $exit = $make_command->make_migration();
        ob_get_clean();

        $this->assertSuccess($exit);

        $make_command = new MakeCommand($this->argv('omega make:migration guest --update'));
        ob_start();
        $exit = $make_command->make_migration();
        ob_get_clean();

        $this->assertSuccess($exit);
    }
}
