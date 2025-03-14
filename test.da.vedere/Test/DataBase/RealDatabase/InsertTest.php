<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;

final class InsertTest extends \RealDatabaseConnectionTest
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanInsertData()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'adriano',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        $this->assertUserExist('adriano');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanInsertMultyRaw()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user' => 'adriano',
                    'pwd'  => 'secret',
                    'stat' => 1,
                ], [
                    'user' => 'giovannini',
                    'pwd'  => 'secret',
                    'stat' => 2,
                ],
            ])
            ->execute();

        $this->assertUserExist('adriano');
        $this->assertUserExist('giovannini');
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanReplaceOnExistData()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'adriano',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'adriano',
                'pwd'  => 'secret',
                'stat' => 66,
            ])
            ->on('stat')
            ->execute();

        $this->assertUserStat('adriano', 66);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanUpdateInsertusingOneQuery()
    {
        MyQuery::from('users', $this->pdo)
            ->insert()
            ->values([
                'user' => 'adriano',
                'pwd'  => 'secret',
                'stat' => 99,
            ])
            ->execute();

        MyQuery::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user' => 'adriano',
                    'pwd'  => 'secret',
                    'stat' => 66,
                ],
                [
                    'user' => 'adriano2',
                    'pwd'  => 'secret',
                    'stat' => 66,
                ],
            ])
            ->on('user')
            ->on('stat')
            ->execute();

        $this->assertUserStat('adriano', 66);
        $this->assertUserExist('adriano2');
    }
}
