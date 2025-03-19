<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console\Output;

use InvalidArgumentException;

use function fwrite;
use function get_resource_type;
use function is_resource;
use function str_contains;
use function stream_get_meta_data;
use function stream_isatty;

use const STDOUT;

/**
 * Output class.
 *
 * Implements the OutputInterface to handle console output operations.
 *
 * This class provides functionality to write text to a specified output stream
 * and determine if the stream is interactive. It ensures that the provided
 * stream is valid and writable.
 *
 * @category   System
 * @package    Console
 * @subpackage Output
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class Output implements OutputInterface
{
    /** @var resource The output stream resource. */
    private mixed $stream;

    /**
     * Initializes the output stream.
     *
     * @param resource $stream The stream resource to be used for output. Defaults to STDOUT.
     * @return void
     * @throws InvalidArgumentException If the provided stream is not valid or not writable.
     */
    public function __construct(mixed $stream = STDOUT)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException(
                'Expected a valid stream'
            );
        }

        $meta = stream_get_meta_data($stream);
        if (str_contains($meta['mode'], 'r') && !str_contains($meta['mode'], '+')) {
            throw new InvalidArgumentException(
                'Expected a writable stream'
            );
        }

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException If writing to the stream fails.
     */
    public function write(string $buffer): void
    {
        if (fwrite($this->stream, $buffer) === false) {
            throw new InvalidArgumentException(
                'Failed to write to stream'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function isInteractive(): bool
    {
        return stream_isatty($this->stream);
    }
}
