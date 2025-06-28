<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use Omega\Database\Connection as BasePDO;

class SchemaConnection extends BasePDO
{
    /**
     * @param array<string, string> $config
     */
    public function __construct(array $config)
    {
        $host             = $config['host'];
        $user             = $config['user'];
        $pass             = $config['password'];

        $this->config = $config;
        $dsn           = "mysql:host=$host;charset=utf8mb4";
        $this->useDsn($dsn, $user, $pass);
    }
}
