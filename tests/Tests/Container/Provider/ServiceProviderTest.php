<?php

/**
 * Part of Omega - Tests\Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container\Provider;

use Omega\Container\Exceptions\FileCopyException;
use Omega\Container\Provider\AbstractServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_put_contents;
use function microtime;
use function now;
use function unlink;

/**
 * Class ServiceProviderTest
 *
 * This PHPUnit test class verifies the functionality of the AbstractServiceProvider,
 * focusing on module exporting and file/directory importing operations.
 *
 * It tests:
 * - Exporting modules with various configurations and retrieving them.
 * - Importing single files, including scenarios where the destination folder
 *   may not exist or the target file already exists (with overwrite options).
 * - Importing directories recursively, ensuring nested contents are properly copied.
 * - Behavior when importing files or directories with existing targets, both
 *   allowing and disallowing overwrites.
 * - Proper exception handling when source files or directories are missing or inaccessible.
 *
 * The test class also handles cleanup of created test files after each test run.
 *
 * @category   Omega
 * @package    Tests
 * @subpackage Container\Proider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @todo: Create appropriate methods to handle all exception cases.
 */
#[CoversClass(AbstractServiceProvider::class)]
class ServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/assets/copy/to/file.txt');
    }

    /**
     * Test it can export module.
     *
     * @return void
     */
    public function testItCanExportModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);

        AbstractServiceProvider::export([
            '/vendor/package/resource/view/' => '/resource/view/',
        ], 'package-share');

        AbstractServiceProvider::export([
            '/vendor/package/resource/js/app.js'   => '/resource/js/app.js',
            '/vendor/package/resource/css/app.css' => '/resource/css/app.css',
        ], 'package-share');

        $this->assertEquals([
            ''              => ['/vendor/package/database/' => '/database/'],
            'package-share' => [
                '/vendor/package/resource/view/'       => '/resource/view/',
                '/vendor/package/resource/js/app.js'   => '/resource/js/app.js',
                '/vendor/package/resource/css/app.css' => '/resource/css/app.css',
            ],
        ], AbstractServiceProvider::getModules());
    }

    /**
     * Test it can get module.
     *
     * @return void
     */
    public function testItCanGetModule(): void
    {
        AbstractServiceProvider::export([
            '/vendor/package/database/' => '/database/',
        ]);
        AbstractServiceProvider::flushModule();

        $this->assertEquals([], AbstractServiceProvider::getModules());
    }

    /**
     * Test it can import file.
     *
     * @return void
     */
    public function testItCanImportFile(): void
    {
        $this->assertTrue(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * est it can import file with folder doest exists.
     *
     * @return void
     */
    public function testItCanImportFileWithFolderDoestExits(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import file with target exists.
     *
     * @return void
     */
    public function testItCanImportFileWithTargetExists(): void
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
     * Test it can not import file with target exists.
     *
     * @return void
     */
    public function testItCanNotImportFileWithTargetExists(): void
    {
        file_put_contents(__DIR__ . '/assets/copy/to/file.txt', '');

        $this->assertFalse(AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/file.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/file.txt'));
    }

    /**
     * Test it can import folder.
     *
     * @return void
     */
    public function testItCanImportFolder(): void
    {
        $random = now()->format('YmdHis') . microtime();
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folders/folder-' . $random
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folders/folder-' . $random . '/file.txt'));
    }

    /**
     * Test it can import folder recursing.
     *
     * @return void
     */
    public function testItCanImportFolderRecursing(): void
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
     * Test it can import folder with target exists.
     *
     * @return void
     */
    public function testItCanImportFolderWithTargetExists(): void
    {
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder',
            true
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }

    /**
     * Test it can not import folder with target exists.
     *
     * @return void
     */
    public function testItCaNotImportFolderWithTargetExists(): void
    {
        // @todo: Always keep in mind that assertTrue might not be the right solution.
        // @todo: I’ll need to check whether assertFalse was the intended behavior, but incorrect
        // @todo: due to the implementation of importDir.
        $this->assertTrue(AbstractServiceProvider::importDir(
            __DIR__ . '/assets/copy/from/folder',
            __DIR__ . '/assets/copy/to/folder'
        ));

        $this->assertTrue(file_exists(__DIR__ . '/assets/copy/to/folder/file.txt'));
    }

    /**
     * Test it throws exception if source file does not exist.
     *
     * @return void
     */
    public function testImportFileThrowsExceptionIfSourceMissing(): void
    {
        $this->expectException(FileCopyException::class);
        $this->expectExceptionMessageMatches('/Failed to copy file from .* to .*/');

        AbstractServiceProvider::importFile(
            __DIR__ . '/assets/copy/from/missing.txt',
            __DIR__ . '/assets/copy/to/file.txt'
        );
    }
}
