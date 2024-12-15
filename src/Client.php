<?php

declare(strict_types = 1);

namespace Jojomi\Dbl;

use Jojomi\Dbl\Query\Query;
use PDO;
use PDOException;
use RuntimeException;
use function getenv;

/**
 * Client.
 */
final class Client
{

    private ?PDO $connection = null;
    private int $transactionLevel = 0;

    public function __construct(private readonly Credentials $credentials, private readonly ?int $port = null)
    {
        // NOOP
    }

    public static function fromEnv(): self
    {
        $port = getenv('MARIADB_PORT');

        return new self(Credentials::fromEnv(), $port === false ? null : (int)$port);
    }

    /**
     * @template T
     *
     * @param \Jojomi\Dbl\Query\Query<T> $query
     *
     * @return T
     */
    public function execute(Query $query): mixed
    {
        try {
            return $query->execute($this);
        } catch (PDOException $e) {
            throw new RuntimeException('Query failed: ' . $query, previous: $e);
        }
    }

    public function beginTransaction(): bool
    {
        if ($this->transactionLevel > 0) {
            $this->transactionLevel++;
            return true;
        }
        $good = $this->getConnection()->beginTransaction();
        if ($good === true) {
            $this->transactionLevel++;
        }
        return $good;
    }

    public function commit(): bool
    {
        if ($this->transactionLevel > 1) {
            $this->transactionLevel--;
            return true;
        }

        $conn = $this->connection;
        if ($conn === null) {
            return false;
        }
        $good = $conn->commit();
        if ($good === true) {
            $this->transactionLevel--;
        }
        return $good;
    }

    public function rollBack(): bool
    {
        if ($this->transactionLevel > 1) {
            $this->transactionLevel--;
            return true;
        }

        $conn = $this->connection;
        if ($conn === null) {
            return false;
        }
        $good = $conn->rollBack();
        if ($good === true) {
            $this->transactionLevel--;
        }
        return $good;
    }

    public function getConnection(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%d;charset=utf8mb4',
            $this->credentials->hostname,
            $this->credentials->database,
            // phpcs:ignore SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator.RequiredNumericLiteralSeparator
            $this->port ?? 3306,
        );

        try {
            $this->connection = new PDO(
                $dsn, $this->credentials->username, $this->credentials->password, [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Could not connect to database: ' . $e->getMessage());
        }

        return $this->connection;
    }

    public function closeConnection(): void
    {
        $this->connection = null;
    }

}
