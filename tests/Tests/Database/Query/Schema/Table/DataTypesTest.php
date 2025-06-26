<?php

declare(strict_types=1);

namespace Tests\Database\Query\Schema\Table;

use Omega\Database\MySchema\Table\Create;
use Tests\Database\AbstractDatabaseQuery;

class DataTypesTest extends AbstractDatabaseQuery
{
    /** @test */
    public function testItCanGenerateQueryUsingAddColumn()
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('name')->varchar(40);
        $schema('size')->enum(['x-small', 'small', 'medium', 'large', 'x-large']);

        $this->assertEquals(
            "CREATE TABLE testing_db.test ( name varchar(40), size ENUM ('x-small', 'small', 'medium', 'large', 'x-large') )",
            $schema->__toString()
        );
    }
}
