<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Commands\VendorImportCommand;
use Omega\Container\Provider\AbstractServiceProvider;
use PHPUnit\Framework\TestCase;

class VendorImportCommandsTest extends TestCase
{
    private ?string $base_path;

    protected function setUp(): void
    {
        $this->base_path = dirname(__DIR__);
    }

    protected function tearDown(): void
    {
        AbstractServiceProvider::flushModule();
        @unlink($this->base_path . '/assets/copy/to/file.txt');
    }

    /**
     * Test it can import.
     *
     * @return void
     */
    public function testItCanImport(): void
    {
        $publish = new VendorImportCommand(['omega', 'vendor:import', '--tag=test'], [
            'force' => false,
        ]);
        $random = now()->format('YmdHis') . microtime();

        AbstractServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/file.txt' => $this->base_path . '/assets/copy/to/file.txt'],
            tag: 'test'
        );
        AbstractServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/folder' => $this->base_path . '/assets/copy/to/folders/folder-' . $random],
            tag: 'test'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/file.txt'));
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import with tag.
     *
     * @return void
     */
    public function testItCanImportWithTag(): void
    {
        $publish = new VendorImportCommand(['omega', 'vendor:import', '--tag=test'], [
            'force' => false,
            'tag'   => 'test',
        ]);
        $random = now()->format('YmdHis') . microtime();

        AbstractServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/file.txt' => $this->base_path . '/assets/copy/to/file.txt'],
            tag: 'test'
        );
        AbstractServiceProvider::export(
            path: [$this->base_path . '/assets/copy/from/folder' => $this->base_path . '/assets/copy/to/folders/folder-' . $random],
            tag: 'vendor'
        );

        ob_start();
        $exit = $publish->main();
        ob_get_clean();

        $this->assertEquals(0, $exit);
        $this->assertTrue(file_exists($this->base_path . '/assets/copy/to/file.txt'));
        $this->assertFalse(file_exists($this->base_path . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }
}
