<?php

/**
 * Part of Omega - Tests\Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Database;

use Omega\Database\MyPDO;
use Omega\Database\MySchema;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract base class for unit tests involving database query logic.
 *
 * Provides mock instances of MyPDO and MySchema\MyPDO for isolated testing,
 * without relying on actual database connections.
 *
 * Designed to be extended by tests that require mocking of database behavior.
 *
 * @category  Omega\Tests
 * @package   Database
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
abstract class AbstractDatabaseQuery extends TestCase
{
    /** @var MyPDO&MockObject Mocked instance of the custom MyPDO class. */
    protected MyPDO $pdo;

    /** @var MySchema\MyPDO&MockObject Mocked instance of the schema-aware MySchema\MyPDO class. */
    protected MySchema\MyPDO $pdo_schema;

    /**
     * Set up the test environment before each test.
     *
     * Creates mock objects for MyPDO and MySchema\MyPDO to avoid real database interaction.
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo        = $this->createMock(MyPDO::class);
        $this->pdo_schema = $this->createMock(MySchema\MyPDO::class);
    }
}
