<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use Jojomi\Dbl\Query\BasicDeleteQuery;
use Jojomi\Dbl\Query\BasicInsertQuery;
use Jojomi\Dbl\Query\Query;
use Jojomi\Dbl\SqlStyle;
use Jojomi\Dbl\Statement\DeleteStatement;
use Jojomi\Dbl\Statement\InsertStatement;
use PDO;
use PDOException;
use RuntimeException;
use function sprintf;

/**
 * BasicSqlClient.
 */
abstract class BasicSqlClient implements Client
{
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
            return $query->execute($this);
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf("Query failed: %s\n%s", $e->getMessage(), $query), previous: $e);
        }
    }

    public function executeStatement(DeleteStatement|InsertStatement $statement): void
    {
        $sqlStyle = $this->getSqlStyle();
        try {
            match (true) {
                $statement instanceof DeleteStatement => $this->execute(BasicDeleteQuery::fromStatement($statement->setRenderStyle($sqlStyle))),
                $statement instanceof InsertStatement => $this->execute(BasicInsertQuery::fromStatement($statement->setRenderStyle($sqlStyle))),
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
    }

    abstract public function getSqlStyle(): SqlStyle;
}
