<?php

declare(strict_types=1);

namespace Tests\Database\Query;

use Omega\Database\MyQuery;
use Tests\Database\AbstractDatabaseQuery;

class DeleteTest extends AbstractDatabaseQuery
{
    /** @test */
    public function testItCanDeleteBetween()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end)',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 BETWEEN 1 AND 100)',
            $delete->queryBind()
        );
    }

    /** @test */
    public function testItCanDeleteCompare()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /** @test */
    public function testItCanDeleteEqual()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /** @test */
    public function testItCanDeleteIn()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 IN (:in_0, :in_1))',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 IN (1, 2))',
            $delete->queryBind()
        );
    }

    /** @test */
    public function testItCanDeleteLike()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 LIKE :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            "DELETE FROM test WHERE ( (test.column_1 LIKE 'test') )",
            $delete->queryBind()
        );
    }

    /** @test */
    public function testItCanDeleteWhere()
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE a < :a OR b > :b',
            $delete->__toString(),
            'update with where statement is like'
        );

        $this->assertEquals(
            'DELETE FROM test WHERE a < 1 OR b > 2',
            $delete->queryBind(),
            'update with where statement is like'
        );
    }

    /** @test */
    public function testItCorrectDeleteWithStrictOff(): void
    {
        $delete = MyQuery::from('test', $this->pdo)
            ->delete()
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $delete->__toString(),
            'update statement must have using or statement'
        );

        $this->assertEquals(
            "DELETE FROM test WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $delete->queryBind(),
            'update statement must have using or statement'
        );
    }
}
