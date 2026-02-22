<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use Jojomi\Dbl\Query\Query;use Jojomi\Dbl\Statement\DeleteStatement;use Jojomi\Dbl\Statement\InsertStatement;use PDO;

/**
 * Client interface.
 */
interface Client
{
    public static function fromEnv(): self;

    /**
     * @template T
     *
     * @param Query<T> $query
     *
     * @return T
     */
    public function execute(Query $query): mixed;

    public function executeStatement(DeleteStatement|InsertStatement $statement): void;

    public function resetTransactions(): void;

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollBack(): bool;

    public function getConnection(): PDO;

    public function closeConnection(): void;
}
