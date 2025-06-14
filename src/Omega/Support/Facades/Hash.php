<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Security\Hashing\HashManager;

/**
 * @method static self                                   setDefaultDriver(\Omega\Security\Hashing\HashInterface $driver)
 * @method static self                                   setDriver(string $driver_name, \Omega\Security\Hashing\HashInterface $driver)
 * @method static \Omega\Security\Hashing\HashInterface driver(?string $driver = null)
 * @method static array                                  info(string $hashed_value)
 * @method static string                                 make(string $value, array $options = [])
 * @method static bool                                   verify(string $value, string $hashed_value, array $options = [])
 * @method static bool                                   isValidAlgorithm(string $hash)
 */
final class Hash extends Facade
{
    protected static function getAccessor()
    {
        return HashManager::class;
    }
}
