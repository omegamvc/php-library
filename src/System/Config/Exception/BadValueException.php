<?php

declare(strict_types=1);

namespace System\Config\Exception;

use InvalidArgumentException;

class BadValueException extends InvalidArgumentException implements ConfigExceptionInterface
{
}
