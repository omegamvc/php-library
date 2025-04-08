<?php

declare(strict_types=1);

namespace System\Database\Seeder;

use System\Database\Connection;
use System\Database\Query\Insert;

abstract class AbstractSeeder implements SeederInterface
{
    /**
     * @param Connection $connection
     */
    public function __construct(
        protected Connection $connection
    ) {
    }

    /**
     * @param class-string $className
     * @return void
     */
    public function call(string $className): void
    {
        $class = new $className($this->connection);
        $class->run();
    }

    /**
     * @param string $tableName
     * @return Insert
     */
    public function create(string $tableName): Insert
    {
        return new Insert($tableName, $this->connection);
    }

    /**
     * Run seeder.
     *
     * @return void
     */
    abstract public function run(): void;
}
