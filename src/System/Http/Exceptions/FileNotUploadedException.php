<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use RuntimeException;

class FileNotUploadedException extends RuntimeException
{
    /**
     * Creates a new Exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('File not uploaded `%s`');
    }
}
