<?php

declare(strict_types=1);

namespace System\Test\Integrate\Commands;

use PHPUnit\Framework\TestCase;
use System\Container\ServiceProvider\AbstractServiceProvider;

final class ServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/assets/copy/to/file.txt');
    }

    /**
     * @test
     */
    public function itCanExportModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);

        AbstractServiceProvider::export([
            '/vendor/package/resource/view/' => '/resourve/view/',
        ], 'pacakge-share');

        AbstractServiceProvider::export([
            '/vendor/package/resource/js/app.js'   => '/resourve/js/app.js',
            '/vendor/package/resource/css/app.css' => '/resourve/css/app.css',
        ], 'pacakge-share');

        $this->assertEquals([
            ''              => ['/vendor/package/database/' => '/database/'],
            'pacakge-share' => [
                '/vendor/package/resource/view/'       => '/resourve/view/',
                '/vendor/package/resource/js/app.js'   => '/resourve/js/app.js',
                '/vendor/package/resource/css/app.css' => '/resourve/css/app.css',
            ],
        ], AbstractServiceProvider::getModules());
    }

    /**
     * @test
     */
    public function itCanGetModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);
        AbstractServiceProvider::flushModule();

        $this->assertEquals([], AbstractServiceProvider::getModules());
    }

    /**
     * @test
     */
    public function itCanImportFile(): void
    {
        $this->assertTrue(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFileWithFolderDoestExits(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFileWithTargetExist(): void
    {
        file_put_contents(__DIR__ . '/assets/copy/to/file.txt', '');

        $this->assertTrue(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt',
            true
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanNotImportFileWithTargetExist(): void
    {
        file_put_contents(__DIR__ . '/assets/copy/to/file.txt', '');

        $this->assertFalse(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolder(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolderRecursing(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder-nest',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/folder/file.txt'));
    }

    /**
     * @test
     */
    public function itCanImportFolderWithTargetExist(): void
    {
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder',
            true
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }

    /**
     * @test
     */
    public function itCaNotImportFolderWithTargetExist(): void
    {
        $this->assertFalse(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }
}
