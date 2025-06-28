<?php

declare(strict_types=1);

namespace Omega\Database\Seeder;

use Omega\Database\Connection;
use Omega\Database\Query\Insert;

abstract class Seeder
{
    protected Connection $pdo;

    public function __construct(Connection $pdo)
    {
        $this->pdo =  $pdo;
    }

    /**
     * @param class-string $className
     */
    public function call(string $className): void
    {
        $class = new $className($this->pdo);
        $class->run();
    }

    public function create(string $tableName): Insert
    {
        return new Insert($tableName, $this->pdo);
    }

    /**
     * Run seeder.
     */
    abstract public function run(): void;
}
