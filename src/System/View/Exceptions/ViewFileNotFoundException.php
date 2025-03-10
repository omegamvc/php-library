<?php

declare(strict_types=1);

namespace System\View\Exceptions;

use InvalidArgumentException;

use function sprintf;

class ViewFileNotFoundException extends InvalidArgumentException
{
    /**
     * Creates a new Exception instance.
     *
     * @param string $fileName
     * @return void
     */
    public function __construct(string $fileName)
    {
        parent::__construct(sprintf('View path not exists `%s`', $fileName));
    }
}
