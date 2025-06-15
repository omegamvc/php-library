<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use function constant;

trait ConstantTrait
{
    /**
     * Get constant from string.
     *
     * @param string $name
     * @return string|int Hex code
     */
    public static function getConstant(string $name): string|int
    {
        return constant("self::$name");
    }
}