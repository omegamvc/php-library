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
     * @param class-string $class_name
     */
    public function call(string $class_name): void
    {
        $class = new $class_name($this->pdo);
        $class->run();
    }

    public function create(string $table_name): Insert
    {
        return new Insert($table_name, $this->pdo);
    }

    /**
     * Run seeder.
     */
    abstract public function run(): void;
}
