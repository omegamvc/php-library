<?php

/**
 * Part of Omega - Tests\Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Database\Query;

use Omega\Database\MyQuery;
use Omega\Database\MyQuery\InnerQuery;
use Omega\Database\MyQuery\Join\CrossJoin;
use Omega\Database\MyQuery\Join\FullJoin;
use Omega\Database\MyQuery\Join\InnerJoin;
use Omega\Database\MyQuery\Join\LeftJoin;
use Omega\Database\MyQuery\Join\RightJoin;
use Omega\Database\MyQuery\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for validating SQL JOIN clause generation using the MyQuery builder.
 *
 * This class verifies the correct generation of SQL queries involving various types of JOINs,
 * including INNER, LEFT, RIGHT, FULL OUTER, and CROSS JOINs. It also ensures support for
 * multiple joins, joins with WHERE conditions, subqueries, and JOIN usage in DELETE and UPDATE clauses.
 *
 * Each test case validates both the raw SQL with bound parameters and the final string with
 * values injected via `queryBind()`.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Schema
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(MyQuery::class)]
#[CoversClass(InnerQuery::class)]
#[CoversClass(CrossJoin::class)]
#[CoversClass(FullJoin::class)]
#[CoversClass(InnerJoin::class)]
#[CoversClass(LeftJoin::class)]
#[CoversClass(RightJoin::class)]
#[CoversClass(Select::class)]
class JoinTest extends AbstractDatabaseQuery
{
    /**
     * Test it can generate inner join
     *
     * @return void
     */
    public function testItCanGenerateInnerJoin(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate left join
     *
     * @return void
     */
    public function testItCanGenerateLeftJoin(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(LeftJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table LEFT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table LEFT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate right join
     *
     * @return void
     */
    public function testItCanGenerateRightJoin(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(RightJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table RIGHT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table RIGHT JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate full join
     *
     * @return void
     */
    public function testItCanGenerateFullJoin(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(FullJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table FULL OUTER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table FULL OUTER JOIN join_table ON base_table.base_id = join_table.join_id',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate cross join.
     *
     * @return void
     */
    public function testItCanGenerateCrossJoin(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(CrossJoin::ref('join_table', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table CROSS JOIN join_table',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table CROSS JOIN join_table',
            $join->queryBind()
        );
    }

    /**
     * Test it can join multiple.
     *
     * @return void
     */
    public function testItCanJoinMultiple(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(InnerJoin::ref('join_table_1', 'base_id', 'join_id'))
            ->join(InnerJoin::ref('join_table_2', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table_1 ON base_table.base_id = join_table_1.join_id INNER JOIN join_table_2 ON base_table.base_id = join_table_2.join_id',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table_1 ON base_table.base_id = join_table_1.join_id INNER JOIN join_table_2 ON base_table.base_id = join_table_2.join_id',
            $join->queryBind()
        );
    }

    /**
     * Test it can join with condition.
     *
     * @return void
     */
    public function testItCanJoinWithCondition(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->equal('a', 1)
            ->join(InnerJoin::ref('join_table_1', 'base_id', 'join_id'))
        ;

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table_1 ON base_table.base_id = join_table_1.join_id WHERE ( (base_table.a = :a) )',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN join_table_1 ON base_table.base_id = join_table_1.join_id WHERE ( (base_table.a = 1) )',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate inner join with sub query.
     *
     * @return void
     */
    public function testItCanGenerateInnerJoinWithSubQuery(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->select()
            ->join(InnerJoin::ref(
                new InnerQuery(
                    (new Select('join_table', ['join_id'], $this->pdo))->in('join_id', [1, 2]),
                    'join_table'
                ),
                'base_id',
                'join_id'
            ))
            ->order('base_id')
        ;

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN (SELECT join_id FROM join_table WHERE (join_table.join_id IN (:in_0, :in_1))) AS join_table ON base_table.base_id = join_table.join_id ORDER BY base_table.base_id ASC',
            $join->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM base_table INNER JOIN (SELECT join_id FROM join_table WHERE (join_table.join_id IN (1, 2))) AS join_table ON base_table.base_id = join_table.join_id ORDER BY base_table.base_id ASC',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate inner join in delete clause.
     *
     * @return void
     */
    public function testItCanGenerateInnerJoinInDeleteClause(): void
    {
        $join = MyQuery::from('base_table', $this->pdo)
            ->delete()
            ->alias('bt')
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
            ->equal('join_table.a', 1)
        ;

        $this->assertEquals(
            'DELETE bt FROM base_table AS bt INNER JOIN join_table ON bt.base_id = join_table.join_id WHERE ( (join_table.a = :join_table__a) )',
            $join->__toString()
        );

        $this->assertEquals(
            'DELETE bt FROM base_table AS bt INNER JOIN join_table ON bt.base_id = join_table.join_id WHERE ( (join_table.a = 1) )',
            $join->queryBind()
        );
    }

    /**
     * Test it can generate inner join in update clause.
     *
     * @return void
     */
    public function testItCanGenerateInnerJoinInUpdateClause(): void
    {
        $update = MyQuery::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->join(InnerJoin::ref('join_table', 'base_id', 'join_id'))
            ->equal('test.column_1', 100)
        ;

        $this->assertEquals(
            'UPDATE test INNER JOIN join_table ON test.base_id = join_table.join_id SET a = :bind_a WHERE ( (test.column_1 = :test__column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            'UPDATE test INNER JOIN join_table ON test.base_id = join_table.join_id SET a = \'b\' WHERE ( (test.column_1 = 100) )',
            $update->queryBind()
        );
    }
}
