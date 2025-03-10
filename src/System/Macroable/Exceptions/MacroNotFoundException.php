<?php

declare(strict_types=1);

namespace System\Macroable\Exceptions;

use InvalidArgumentException;

class MacroNotFoundException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @param string $methodName
     * @return void
     */
    public function __construct(string $methodName)
    {
        parent::__construct(sprintf('Macro `%s` is not macro able.', $methodName));
    }
}
