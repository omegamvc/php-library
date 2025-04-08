<?php

namespace System\Database\Seeder;

use System\Database\Query\Insert;

interface SeederInterface
{
    /**
     * @param class-string $className
     * @return void
     */
    public function call(string $className): void;

    /**
     * @param string $tableName
     * @return Insert
     */
    public function create(string $tableName): Insert;

    /**
     * Run seeder.
     *
     * @return void
     */
    public function run(): void;
}
