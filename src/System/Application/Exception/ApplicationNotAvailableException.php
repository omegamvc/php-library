<?php

declare(strict_types=1);

namespace System\Application\Exception;

use RuntimeException;

class ApplicationNotAvailableException extends RuntimeException
{
    /**
     * Creates a new Exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Application not start yet!');
    }
}
