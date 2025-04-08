<?php

declare(strict_types=1);

namespace System\Database\Schema;

use System\Database\Connection;

class SchemaConnection extends Connection
{
    /**
     * @param array<string, string> $configs
     */
    public function __construct(array $configs)
    {
        parent::__construct($configs);

        $host             = $configs['host'];
        $user             = $configs['user'];
        $pass             = $configs['password'];

        $this->configs = $configs;
        $dsn           = "mysql:host=$host;charset=utf8mb4";
        $this->useDsn($dsn, $user, $pass);
    }
}
