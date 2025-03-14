<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery;

final class ReplaceTest extends \RealDatabaseConnectionTest
{
    /**
     * @test
     *
     * @group database
     */
    public function itCanReplaceOnNewData()
    {
        MyQuery::from('users', $this->pdo)
            ->replace()
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
            ->replace()
            ->values([
                'user' => 'adriano',
                'pwd'  => 'secret',
                'stat' => 66,
            ])
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
            ->replace()
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
            ->execute();

        $this->assertUserStat('adriano', 66);
        $this->assertUserExist('adriano2');
    }
}
