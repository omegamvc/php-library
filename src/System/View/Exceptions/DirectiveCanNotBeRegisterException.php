<?php

declare(strict_types=1);

namespace System\View\Exceptions;

use InvalidArgumentException;

class DirectiveCanNotBeRegisterException extends InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name
     * @param string $useBy
     * @return void
     */
    public function __construct(string $name, string $useBy)
    {
        parent::__construct("Directive '$name' cant be use, this has been use in '$useBy'.");
    }
}
