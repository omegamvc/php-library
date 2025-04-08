<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use Exception;

class StreamedResponseCallableException extends Exception
{
    /**
     * Creates a new Exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Stream callback must not be null');
    }
}
