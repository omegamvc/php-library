<?php

declare(strict_types=1);

namespace System\View\Exceptions;

use InvalidArgumentException;

class DirectiveNotRegisterException extends InvalidArgumentException
{
    /**
     * @param string $name
     * @return void
     */
    public function __construct(string $name)
    {
        parent::__construct("Directive '$name' is not registered.");
    }
}
