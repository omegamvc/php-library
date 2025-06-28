<?php

declare(strict_types=1);

namespace Omega\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

use function call_user_func;
use function is_bool;
use function is_int;
use function is_null;
use function microtime;
use function round;

class Connection
{
    /** @var PDO PDO */
    private PDO $pdo;

    /** @var PDOStatement */
    private PDOStatement $statement;

    /**
     * Connection configuration.
     *
     * @var array<string, string>
     */
    protected array $config;

    /**
     * Query prepare statement;.
     */
    protected string $query;

    /**
     * Log query when execute and fetching.
     * - query.
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $logs = [];

    /**
     * @param array<string, string> $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $databaseName = $config['database_name'];
        $host         = $config['host'];
        $user         = $config['user'];
        $pass         = $config['password'];

        $this->config = $config;
        $dsn           = "mysql:host=$host;dbname=$databaseName";
        $this->useDsn($dsn, $user, $pass);
    }

    /**
     * Singleton pattern implement for Database connation.
     *
     * @return self
     */
    public function getInstance(): static
    {
        return $this;
    }

    /**
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @return $this
     * @throws Exception
     */
    protected function useDsn(string $dsn, string $user, string $pass): self
    {
        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $option);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * Create connection using static.
     *
     * @param array<string, string> $config
     * @return Connection
     * @throws Exception
     */
    public static function conn(array $config): Connection
    {
        return new self($config);
    }

    /**
     * Get connection configuration.
     *
     * @return array<string, string>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function query(string $query): self
    {
        $this->statement = $this->pdo->prepare($this->query = $query);

        return $this;
    }

    /**
     * @param bool|int|string|null $param
     * @param mixed                $value
     * @param bool|int|string|null $type
     * @return $this
     */
    public function bind(bool|int|string|null $param, mixed $value, bool|int|string $type = null): self
    {
        if (is_null($type)) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };
        }
        $this->statement->bindValue($param, $value, $type);

        return $this;
    }

    /**
     * @return bool True if success
     * @throws PDOException
     */
    public function execute(): bool
    {
        $start    = microtime(true);
        $execute  = $this->statement->execute();
        $elapsed  = round((microtime(true) - $start) * 1000, 2);

        $this->addLog($this->query, $elapsed);

        return $execute;
    }

    /**
     * @return array|false
     */
    public function resultSet(): array|false
    {
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return mixed
     */
    public function single(): mixed
    {
        $this->execute();

        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return int the number of rows
     */
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * @return string|false last id
     */
    public function lastInsertId(): false|string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @return bool Transaction status
     */
    public function transaction(callable $callable): bool
    {
        if (false === $this->beginTransaction()) {
            return false;
        }

        $return_call =  call_user_func($callable);
        if (false === $return_call) {
            return $this->cancelTransaction();
        }

        return $this->endTransaction();
    }

    /**
     * Initiates a transaction.
     *
     * @return bool True if success
     * @throws PDOException
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool True if success
     * @throws PDOException
     */
    public function endTransaction(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool True if success
     * @throws PDOException
     */
    public function cancelTransaction(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @param float $elapsed_time
     * @return void
     */
    protected function addLog(string $query, float $elapsed_time): void
    {
        $this->logs[] = [
            'query' => $query,
            'time'  => $elapsed_time,
        ];
    }

    /**
     * Flush logs query.
     */
    public function flushLogs(): void
    {
        $this->logs = [];
    }

    /**
     * Get logs query.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }
}
