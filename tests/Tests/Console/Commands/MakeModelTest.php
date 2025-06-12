<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\MakeCommand;

class MakeModelTest extends CommandTestHelper
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($model = __DIR__ . '/assets/User2.php')) {
            unlink($model);
        }
    }

    /**
     * Test it can call make command model with success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithSuccess(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User2'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * Test it can call make command model with exist model.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithExistModel(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User --table-name=users --force'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__ . '/assets/User.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User extends Model', $model);
    }

    /**
     * est it can call make command model with table name and return success.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelWithTableNameAndReturnSuccess(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model User2 --table-name users'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertSuccess($exit);

        $file = __DIR__  . '/assets/User2.php';
        $this->assertTrue(file_exists($file));

        $model = file_get_contents($file);
        $this->assertContain('class User2 extends Model', $model);
    }

    /**
     * Test it can call make command model return fails.
     *
     * @return void
     */
    public function testItCanCallMakeCommandModelReturnFails(): void
    {
        $make_model = new MakeCommand($this->argv('omega make:model Asset'));
        ob_start();
        $exit = $make_model->make_model();
        ob_get_clean();

        $this->assertFails($exit);
    }
}
