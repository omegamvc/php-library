<?php

declare(strict_types=1);

namespace System\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use function is_bool;
use function is_int;
use function is_null;

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
    protected array $configs;

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
     * @param array<string, string> $configs
     * @throws Exception
     */
    public function __construct(array $configs)
    {
        $database_name    = $configs['database_name'];
        $host             = $configs['host'];
        $user             = $configs['user'];
        $pass             = $configs['password'];

        $this->configs = $configs;
        $dsn           = "mysql:host=$host;dbname=$database_name";
        $this->useDsn($dsn, $user, $pass);
    }

    /**
     * Singleton pattern implement for Database connection.
     *
     * @return self
     */
    public function instance(): static
    {
        return $this;
    }

    /**
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
     * @param array<string, string> $configs
     * @return Connection
     * @throws Exception
     */
    public static function conn(array $configs): Connection
    {
        return new self($configs);
    }

    /**
     * Get connection configuration.
     *
     * @return array<string, string>
     */
    public function configs(): array
    {
        return $this->configs;
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
     * @param int|string|bool|null $param
     * @param mixed                $value
     * @param int|string|bool|null $type
     * @return $this
     */
    public function bind(int|string|bool|null $param, mixed $value, int|string|bool|null $type = null): self
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
    public function getLastInsertId(): false|string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param callable $callable
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
     *
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
     *
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
     *
     * @throws PDOException
     */
    public function cancelTransaction(): bool
    {
        return $this->pdo->rollBack();
    }

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
