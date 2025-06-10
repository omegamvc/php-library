<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Database\MyPDO;
use Omega\Database\MySchema\Table\Create;
use Omega\Integrate\Application;
use Omega\Console\Commands\MigrationCommand;
use Omega\Support\Facades\Facade;
use Omega\Support\Facades\Schema;
use Tests\Database\AbstractDatabaseTest;
use Omega\Text\Str;

require_once __DIR__ . '/../../Database/AbstractDatabaseTest.php';

final class MigrationCommandsTest extends AbstractDatabaseTest
{
    private Application $app;

    protected function setUp(): void
    {
        $this->createConnection();

        $this->app = new Application(__DIR__);
        $this->app->setMigrationPath('/assets/database/migration/');
        $this->app->set('environment', 'dev');
        $this->app->set(MyPDO::class, fn () => $this->pdo);
        $this->app->set('MySchema', fn () => $this->schema);
        $this->app->set('dsn.sql', fn () => $this->env);

        Facade::setFacadeBase($this->app);
        Schema::table('migration', function (Create $column) {
            $column('migration')->varchar(100)->notNull();
            $column('batch')->int(4)->notNull();

            $column->unique('migration');
        })->execute();
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
        Schema::drop()->table('migration')->ifExists()->execute();
        MigrationCommand::flushVendorMigrationPaths();
        $this->app->flush();
    }

    /**
     * Test it can run migration return success and success migrate
     *
     * @return void
     */
    public function testItCanRunMigrationReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate']);
        ob_start();
        $exit = $migrate->main();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration fresh return success and success migrate.
     *
     * @return void
     */
    public function testItCanRunMigrationFreshReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:fresh']);
        ob_start();
        $exit = $migrate->fresh(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration reset return success and success migrate,
     *
     * @return void
     */
    public function testItCanRunMigrationResetReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:reset']);
        ob_start();
        $exit = $migrate->reset();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration refresh return success and success migrate.
     *
     * @rreturn void
     */
    public function testItCanRunMigrationRefreshReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:refresh']);
        ob_start();
        $exit = $migrate->refresh();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run migration rollback return success and success migrate.
     *
     * @return void
     */
    public function testItCanRunMigrationRollbackReturnSuccessAndSuccessMigrate(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:rollback', '--batch=0']);
        ob_start();
        $exit = $migrate->rollback();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }

    /**
     * Test it can run database create.
     *
     * @return void
     */
    public function testItCanRunDatabaseCreate(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:create']);
        ob_start();
        $exit = $migrate->databaseCreate(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success create database `testing_db`'));
    }

    /**
     * Test it can run database show.
     *
     * @return void
     */
    public function testItCanRunDatabaseShow(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:show']);
        ob_start();
        $exit = $migrate->databaseShow();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'migration'));
    }

    /**
     * Test it can run database drop.
     *
     * @return void
     */
    public function testItCanRunDatabaseDrop(): void
    {
        $migrate = new MigrationCommand(['omega', 'db:drop']);
        ob_start();
        $exit = $migrate->databaseDrop(true);
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'success drop database `testing_db`'));
    }

    /**
     * Test it can run migration init.
     *
     * @retunr void
     */
    public function testItCanRunMigrateInit(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate:init']);
        ob_start();
        $exit    = $migrate->initializeMigration();
        $out     = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, 'Migration table already exist on your database table.'));
    }

    /**
     * Test it can pass confirmation using option yes.
     *
     * @return void
     */
    public function testItCanPassConfirmationUsingOptionYes(): void
    {
        $confirmation = (fn () => $this->{'confirmation'}('message?'))->call(new MigrationCommand(['omega', 'db:create'], ['yes' => true]));
        $this->assertTrue($confirmation);
    }

    /**
     * Test it can run migration from vendor.
     *
     * @return void
     */
    public function testItCanRunMigrationFromVendor(): void
    {
        $migrate = new MigrationCommand(['omega', 'migrate']);
        MigrationCommand::addVendorMigrationPath(__DIR__ . '/assets/database/vendor-migration/');
        ob_start();
        $exit = $migrate->main();
        $out  = ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(Str::contains($out, '2023_08_07_181000_users'));
        $this->assertTrue(Str::contains($out, '2024_06_12_070600_clients'));
        $this->assertTrue(Str::contains($out, 'DONE'));
    }
}
