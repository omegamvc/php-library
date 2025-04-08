<?php

declare(strict_types=1);

namespace System\Database\Query;

use System\Database\Connection;

/**
 * Query Builder.
 */
class Query
{
    public const ORDER_ASC   = 0;
    public const ORDER_DESC  = 1;
    /** @var Connection */
    protected $PDO;

    /**
     * Create new Builder.
     *
     * @param Connection $PDO the PDO connection
     */
    public function __construct(Connection $PDO)
    {
        $this->PDO = $PDO;
    }

    /**
     * Create builder using invoke.
     *
     * @param string $table_name Table name
     *
     * @return Table
     */
    public function __invoke(string $table_name)
    {
        return $this->table($table_name);
    }

    /**
     * Create builder and set table name.
     *
     * @param string $table_name Table name
     *
     * @return Table
     */
    public function table(string $table_name)
    {
        return new Table($table_name, $this->PDO);
    }

    /**
     * Create Builder using static function.
     *
     * @param string $table_name Table name
     * @param Connection  $PDO        The PDO connection, null give global instance
     *
     * @return Table
     */
    public static function from(string $table_name, Connection $PDO)
    {
        $conn = new Query($PDO);

        return $conn->table($table_name);
    }
}
