<?php

declare(strict_types=1);

namespace Tests\Database\Query\Schema\DB;

use Omega\Database\MySchema\DB\Create;
use Tests\Database\AbstractDatabaseQuery;

class CreateTest extends AbstractDatabaseQuery
{
    public function testItCanGenerateCreateDatabase()
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE test;',
            $schema->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfExists()
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifExists(true)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfExistsFalse()
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifExists(false)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfNotExists()
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF NOT EXISTS test;',
            $schema->ifNotExists(true)->__toString()
        );
    }

    public function testItCanGenerateCreateDatabaseIfNotExistsFalse()
    {
        $schema = new Create('test', $this->pdo_schema);

        $this->assertEquals(
            'CREATE DATABASE IF EXISTS test;',
            $schema->ifNotExists(false)->__toString()
        );
    }
}
