<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use InvalidArgumentException;

use function sprintf;

class FileNotExistsException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @param string $fileLocation
     * @return void
     */
    public function __construct(string $fileLocation)
    {
        parent::__construct(sprintf('File location not exists `%s`', $fileLocation));
    }
}
