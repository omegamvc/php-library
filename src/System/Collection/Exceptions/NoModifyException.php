<?php

declare(strict_types=1);

namespace System\Collection\Exceptions;

use InvalidArgumentException;

class NoModifyException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Collection imutable can not be modify');
    }
}
