<?php

declare(strict_types=1);

namespace System\Text\Exceptions;

use InvalidArgumentException;

class PropertyNotExistException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @param string $propertyName
     * @return void
     */
    public function __construct(string $propertyName)
    {
        parent::__construct(sprintf('Property `%s` not exist.', $propertyName));
    }
}
