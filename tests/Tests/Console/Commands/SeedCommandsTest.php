<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\SeedCommand;

final class SeedCommandsTest extends CommandTest
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $migration = __DIR__ . '/assets/seeders/';
        array_map('unlink', glob("$migration/*.php"));
    }

    /**
     * Test it can call make command seeder with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeederWithSuccess(): void
    {
        $makeCommand = new SeedCommand($this->argv('omega make:seed BaseSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/seeders/BaseSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class BaseSeeder extends Seeder', $class);
        $this->assertContain('public function run(): void', $class);
    }

    /**
     * Test it can call make command seed with fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeedWithFails(): void
    {
        $makeCommand = new SeedCommand($this->argv('omega make:seed'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make command seed with fails file exist.
     *
     * @return void
     */
    public function testItCanCallMakeCommandSeedWithFailsFileExist(): void
    {
        app()->setSeederPath(__DIR__ . '//assets//seeders//basic//');
        $makeCommand = new SeedCommand($this->argv('omega make:seed BasicSeeder'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertFails($exit);
    }

    /**
     * Test it can call make exist command seeder.
     *
     * @return void
     */
    public function testItCanCallMakeExistCommandSeeder(): void
    {
        app()->setSeederPath(__DIR__ . '//assets//seeders//');
        $makeCommand = new SeedCommand($this->argv('omega make:seed ExistSeeder --force'));
        ob_start();
        $exit = $makeCommand->make();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '//assets//seeders//ExistSeeder.php';
        $this->assertTrue(file_exists($file));

        $class = file_get_contents($file);
        $this->assertContain('class ExistSeeder extends Seeder', $class);
        $this->assertContain('public function run(): void', $class);
    }
}
