<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;

/**
 * Query Builder.
 */
class Query
{
    public const int ORDER_ASC   = 0;
    public const int ORDER_DESC  = 1;

    /** @var Connection */
    protected Connection $pdo;

    /**
     * Create new Builder.
     *
     * @param Connection $pdo the PDO connection
     */
    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create builder using invoke.
     *
     * @param string $tableName Table name
     *
     * @return Table
     */
    public function __invoke(string $tableName): Table
    {
        return $this->table($tableName);
    }

    /**
     * Create builder and set table name.
     *
     * @param string|InnerQuery $tableName Table name
     *
     * @return Table
     */
    public function table(string|InnerQuery $tableName): Table
    {
        return new Table($tableName, $this->pdo);
    }

    /**
     * Create Builder using static function.
     *
     * @param string|InnerQuery $tableName Table name
     * @param Connection        $pdo       The PDO connection, null give global instance
     * @return Table
     */
    public static function from(string|InnerQuery $tableName, Connection $pdo): Table
    {
        $conn = new Query($pdo);

        return $conn->table($tableName);
    }
}
