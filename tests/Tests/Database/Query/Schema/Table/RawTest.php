<?php

declare(strict_types=1);

namespace Tests\Database\Query\Schema\Table;

use Omega\Database\MySchema\Table\Raw;
use Tests\Database\AbstractDatabaseQuery;

class RawTest extends AbstractDatabaseQuery
{
    /** @test */
    public function testitCanGenerateQueryUsingAddColumn()
    {
        $schema = new Raw('CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )', $this->pdo_schema);

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }
}
