<?php

declare(strict_types=1);

namespace System\Text\Exceptions;

use InvalidArgumentException;

class NoReturnException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @param string $method
     * @param string $originalText
     */
    public function __construct(string $method, string $originalText)
    {
        parent::__construct('Method ' . $method . ' with ' . $originalText . ' doest return anything.');
    }
}
