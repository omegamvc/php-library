<?php

declare(strict_types=1);

namespace Tests\Database\Query\Schema\DB;

use Omega\Database\MySchema\DB\Drop;
use Tests\Database\AbstractDatabaseQuery;

class DropTest extends AbstractDatabaseQuery
{
    public function testItCanGenerateCreateDatabase()
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE test;',
            $schema->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Drop('test', $this->pdo_schema);

        $this->assertEquals(
            'DROP DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
