<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Database\MyPDO;
use Omega\Integrate\Application;
use Omega\Console\Commands\SeedCommand;
use Omega\Support\Facades\DB;
use Omega\Support\Facades\PDO as FacadesPDO;
use Omega\Support\Facades\Schema;
use Tests\Database\AbstractDatabaseTest;
use Omega\Text\Str;

require_once __DIR__ . '/../../Database/AbstractDatabaseTest.php';

class SeedCommandsWithDatabaseTest extends AbstractDatabaseTest
{
    private Application $app;

    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();

        require_once __DIR__ . '/assets/database/seeders/BasicSeeder.php';
        require_once __DIR__ . '/assets/database/seeders/UserSeeder.php';
        require_once __DIR__ . '/assets/database/seeders/ChainSeeder.php';
        require_once __DIR__ . '/assets/database/seeders/CustomNamespaceSeeder.php';
        $this->app = new Application(__DIR__);
        $this->app->setSeederPath(__DIR__ . '/assets/database/seeders/');
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new FacadesPDO($this->app);
        new DB($this->app);
        $this->app->set(MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
        $this->app->flush();
    }

    /**
     * Test it can run seeder.
     *
     * @return void
     */
    public function testItCanRunSeeder(): void
    {

        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'BasicSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder runner with real insert data.
     *
     * @return void
     */
    public function testItCanRunSeederRunnerWithRealInsertData(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'UserSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder with custom namespace.
     *
     * @return void
     */
    public function testItCanRunSeederWithCustomNamespace(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--name-space', 'CustomNamespaceSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }

    /**
     * Test it can run seeder with call other.
     *
     * @return void
     */
    public function testItCanRunSeederWithCallOther(): void
    {
        $seeder = new SeedCommand(['omega', 'db:seed', '--class', 'ChainSeeder']);
        ob_start();
        $seeder->main();
        $out  = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'seed for basic seeder'));
        $this->assertTrue(Str::contains($out, 'Success run seeder'));
    }
}
