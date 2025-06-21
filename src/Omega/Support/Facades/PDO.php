<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\MyPDO;

/**
 * Facade for the PDO-based database access layer.
 *
 * This facade provides a static interface to the custom MyPDO implementation,
 * which abstracts raw PDO logic for consistency and enhanced query execution.
 * Useful for executing queries directly when you don't need ORM or query builder overhead.
 *
 * Example:
 * ```php
 * $pdo = PDO::instance();
 * $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
 * $stmt->execute(['id' => 1]);
 * ```
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static MyPDO instance() Get the singleton instance of the custom MyPDO wrapper.
 */
class PDO extends Facade
{
    /**
     * Get the service accessor key for the MyPDO service.
     *
     * This key is used to retrieve the bound instance from the service container.
     *
     * @return string The MyPDO service class name.
     */
    protected static function getAccessor(): string
    {
        return MyPDO::class;
    }
}
