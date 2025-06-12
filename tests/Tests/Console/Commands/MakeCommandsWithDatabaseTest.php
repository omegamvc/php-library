<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Database\MyPDO;
use Omega\Database\MyQuery;
use Omega\Integrate\Application;
use Omega\Console\Commands\MakeCommand;
use Omega\Support\Facades\PDO;
use Omega\Support\Facades\Schema;
use Tests\Database\AbstractDatabase;
use Omega\Text\Str;

class MakeCommandsWithDatabaseTest extends AbstractDatabase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->createConnection();

        $this->app = new Application(__DIR__);
        $this->app->set('environment', 'dev');
        new Schema($this->app);
        new PDO($this->app);
        $this->app->set(MyPDO::class, $this->pdo);
        $this->app->set('MySchema', $this->schema);
        $this->app->set('dsn.sql', $this->env);
        $this->app->set('MyQuery', fn () => new MyQuery($this->pdo));
        $this->app->setModelPath(DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR);
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
        $this->app->flush();

        if (file_exists($client =  __DIR__ . '/assets/Client.php')) {
            unlink($client);
        }
    }

    /**
     * Test it can call make command model with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithSuccess(): void
    {
        $make_model = new MakeCommand(['omega', 'make:model', 'Client', '--table-name', 'users']);
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertEquals(0, $exit);

        $file = __DIR__ . '/assets/Client.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertTrue(Str::contains($model, 'protected string $' . "table_name  = 'users'"));
        $this->assertTrue(Str::contains($model, 'protected string $' . "primary_key = 'id'"));
    }
}
