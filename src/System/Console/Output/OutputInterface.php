<?php

declare(strict_types=1);

namespace System\Console\Output;

/**
 * inspire by Aydin Hassan <aydin@hotmail.co.uk>.
 *
 * @source https://github.com/php-school/terminal/blob/master/src/IO/OutputStream.php
 */
interface OutputInterface
{
    public function write(string $buffer): void;

    public function isInteractive(): bool;
}
