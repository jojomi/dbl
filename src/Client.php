<?php

declare(strict_types = 1);

namespace Jojomi\Dbl;

use Jojomi\Dbl\Query\BasicDeleteQuery;
use Jojomi\Dbl\Query\BasicInsertQuery;
use Jojomi\Dbl\Query\Query;
use Jojomi\Dbl\Statement\DeleteStatement;
use Jojomi\Dbl\Statement\InsertStatement;
use PDO;
use PDOException;
use RuntimeException;
use function getenv;
use function sprintf;

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
            throw new RuntimeException(sprintf("Query failed: %s\n%s", $e->getMessage(), $query), previous: $e);
        }
    }

    public function executeStatement(DeleteStatement|InsertStatement $statement): void
    {
        try {
            match (true) {
                $statement instanceof DeleteStatement => $this->execute(BasicDeleteQuery::fromStatement($statement)),
                $statement instanceof InsertStatement => $this->execute(BasicInsertQuery::fromStatement($statement)),
            };
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Query failed: %s\n%s", $e->getMessage(), $statement), previous: $e);
        }
    }

    public function resetTransactions(): void
    {
        while ($this->transactionLevel > 0) {
            $this->rollBack();
        }
    }

    public function beginTransaction(): bool
    {
        if ($this->transactionLevel > 0) {
            $this->transactionLevel++;

            return true;
        }
        $conn = $this->getConnection();
        $good = $conn->beginTransaction();
        if ($good === true) {
            $this->transactionLevel++;
        }

        return $good;
    }

    public function commit(): bool
    {
        var_dump($this->transactionLevel);
        if ($this->transactionLevel > 1) {
            $this->transactionLevel--;

            return true;
        }

        $conn = $this->getConnection();
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

            // TODO we need to do more here: prevent more queries executed if already rollbacked?

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
