<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\MaintenanceCommand;

class MaintenanceCommandsTest extends CommandTest
{
    protected function tearDown(): void
    {
        if (file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            unlink($down);
        }
        if (file_exists($maintenance = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php')) {
            unlink($maintenance);
        }
        parent::tearDown();
    }

    /**
     * Test it can make down maintenance mode.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceMode(): void
    {
        $down = new MaintenanceCommand(['down']);

        $this->assertFileDoesNotExist(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileDoesNotExist(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');

        ob_start();
        $this->assertSuccess($down->down());
        ob_get_clean();

        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Test it can make down maintenance mode fresh down config.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceModeFreshDownConfig(): void
    {
        $command = new MaintenanceCommand(['command']);
        ob_start();
        $command->down();

        $start = 0;

        if (file_exists($down = storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down')) {
            $start = filemtime($down);
        }

        $command->down();
        $end = filemtime($down);
        ob_get_clean();

        $this->assertGreaterThanOrEqual($end, $start);
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
    }

    /**
     * Test it can make down maintenance mode fail.
     *
     * @return void
     */
    public function testItCanMakeDownMaintenanceModeFail(): void
    {
        $down = new MaintenanceCommand(['down']);

        ob_start();
        $this->assertSuccess($down->down());
        $this->assertFails($down->down());
        ob_get_clean();
    }

    /**
     * Test it can make up maintenance mode.
     *
     * @return void
     */
    public function testItCanMakeUpMaintenanceMode(): void
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $command->down();

        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'down');
        $this->assertFileExists(storage_path() . 'app' . DIRECTORY_SEPARATOR . 'maintenance.php');
        $this->assertSuccess($command->up());

        ob_get_clean();
    }

    /**
     * Test it can make up maintenance mode but fail.
     *
     * @return void
     */
    public function testItCanMakeUpMaintenanceModeButFail(): void
    {
        $command = new MaintenanceCommand(['up']);

        ob_start();
        $this->assertFails($command->up());
        ob_get_clean();
    }
}
