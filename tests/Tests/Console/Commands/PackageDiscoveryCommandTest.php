<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\PackageDiscoveryCommand;
use Omega\Integrate\Application;
use Omega\Integrate\PackageManifest;
use PHPUnit\Framework\TestCase;

class PackageDiscoveryCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        if (file_exists($file = dirname(__DIR__) . '/assets/app1/bootstrap/cache/packages.php')) {
            @unlink($file);
        }
    }

    /**
     * Test it can create config file.
     *
     * @return void
     */
    public function testItCanCreateConfigFile(): void
    {
        $app = new Application(__DIR__ . '/assets/app1/');

        // overwrite PackageManifest has been set in Application before.
        $app->set(PackageManifest::class, fn () => new PackageManifest(
            base_path: $app->basePath(),
            application_cache_path: $app->getApplicationCachePath(),
            vendor_path: '/package/'
        ));

        $discovery = new PackageDiscoveryCommand(['omega', 'package:discovery']);
        ob_start();
        $out = $discovery->discovery($app);
        ob_get_clean();

        $this->assertEquals(0, $out);

        $app->flush();
    }
}
