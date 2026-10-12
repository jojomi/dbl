<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use Jojomi\Dbl\Query\DeleteQuery;use Jojomi\Dbl\Query\InsertQuery;
use Jojomi\Dbl\Query\Query;
use Jojomi\Dbl\Query\SelectListQuery;
use Jojomi\Dbl\Query\SelectQuery;
use Jojomi\Dbl\SqlStyle;
use Jojomi\Dbl\Statement\DeleteStatement;
use Jojomi\Dbl\Statement\InsertStatement;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;
use function in_array;
use function is_string;
use function sprintf;
use function str_contains;
use function str_starts_with;

/**
 * BasicSqlClient.
 */
abstract class BasicSqlClient implements Client
{
    /**
     * Message fragments of PDO errors caused by a lost connection, for drivers that do not report SQLSTATE class 08.
     */
    private const array CONNECTION_LOST_MESSAGES = [
        'server closed the connection unexpectedly',
        'no connection to the server',
        'SSL connection has been closed unexpectedly',
        'terminating connection',
        'MySQL server has gone away',
        'Lost connection to MySQL server',
    ];

    /**
     * Postgres SQLSTATEs for a server shutting down or terminating the session.
     */
    private const array SHUTDOWN_SQL_STATES = ['57P01', '57P02', '57P03'];

    protected ?PDO $connection = null;
    protected int $transactionLevel = 0;


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
            return $this->executeOnce($query);
        } catch (RuntimeException $e) {
            // The connection is kept open between queries and may have been closed by the server in the meantime.
            // Only reads are retried: whether a write was applied before the connection broke is unknown.
            $isRead = $query instanceof SelectQuery || $query instanceof SelectListQuery;
            if ($this->transactionLevel > 0 || !$isRead || !self::isConnectionLost($e)) {
                throw $e;
            }
            $this->closeConnection();

            return $this->executeOnce($query);
        }
    }

    /**
     * @template T
     *
     * @param \Jojomi\Dbl\Query\Query<T> $query
     *
     * @return T
     */
    private function executeOnce(Query $query): mixed
    {
        try {
            return $query->execute($this);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Query failed: %s\n%s", $e->getMessage(), $query), previous: $e);
        }
    }

    private static function isConnectionLost(Throwable $e): bool
    {
        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof PDOException) {
                $sqlState = $current->errorInfo[0] ?? null;
                $isConnectionClass = is_string($sqlState) && str_starts_with($sqlState, '08');
                if ($isConnectionClass || in_array($sqlState, self::SHUTDOWN_SQL_STATES, true)) {
                    return true;
                }
            }
            // some queries rethrow without keeping the PDOException, so the message is all that is left
            foreach (self::CONNECTION_LOST_MESSAGES as $message) {
                if (str_contains($current->getMessage(), $message)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function executeStatement(DeleteStatement|InsertStatement $statement): void
    {
        $sqlStyle = $this->getSqlStyle();
        try {
            match (true) {
                $statement instanceof DeleteStatement => $this->execute(DeleteQuery::fromStatement($statement->setRenderStyle($sqlStyle))),
                $statement instanceof InsertStatement => $this->execute(InsertQuery::fromStatement($statement->setRenderStyle($sqlStyle))),
            };
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Query failed: %s\n%s", $e->getMessage(), $statement->render($sqlStyle)), previous: $e);
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

        $conn = $this->getConnection();
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

    public function closeConnection(): void
    {
        $this->connection = null;
        // an open transaction ends with its connection
        $this->transactionLevel = 0;
    }

    abstract public function getSqlStyle(): SqlStyle;
}
