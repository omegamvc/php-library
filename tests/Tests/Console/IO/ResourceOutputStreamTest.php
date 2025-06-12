<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\IO;

use InvalidArgumentException;
use Omega\Console\IO\ResourceOutputStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

/**
 * Unit test for the ResourceOutputStream class.
 *
 * This test suite verifies the behavior of the ResourceOutputStream, ensuring:
 * - It accepts valid writable streams and rejects invalid or non-writable ones.
 * - It correctly writes data to the provided stream.
 * - It properly reports its interactive status.
 *
 * These tests help guarantee reliable and consistent output stream handling
 * in environments where resource-based output is used.
 *
 * @category   Omega
 * @package    Tests
 * @subpackage Console\IO
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(ResourceOutputStream::class)]
class ResourceOutputStreamTest extends TestCase
{
    /**
     * Test constructing the ResourceOutputStream with valid stream.
     *
     * @return void
     */
    public function testConstructorWithValidStream(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $this->assertInstanceOf(ResourceOutputStream::class, $outputStream);
        fclose($stream);
    }

    /**
     * Test constructor throws exception for invalid stream.
     *
     * @return void
     */
    public function testConstructorThrowsForInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a valid stream');

        new ResourceOutputStream('invalid_stream');
    }

    /**
     * Test constructor throws exception for non-writable stream.
     *
     * @return void
     */
    public function testConstructorThrowsForNonWritableStream(): void
    {
        $stream = fopen('php://memory', 'r');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a writable stream');

        new ResourceOutputStream($stream);

        fclose($stream);
    }

    /**
     * Test writing to a valid stream.
     *
     * @return void
     */
    public function testWriteToStream(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $outputStream->write('Hello, World!');

        rewind($stream);
        $this->assertEquals('Hello, World!', stream_get_contents($stream));

        fclose($stream);
    }

    /**
     * Test if the stream is interactive.
     *
     * @return void
     */
    public function testIsInteractive(): void
    {
        $stream       = fopen('php://memory', 'w+');
        $outputStream = new ResourceOutputStream($stream);

        $this->assertFalse($outputStream->isInteractive());

        fclose($stream);
    }
}
