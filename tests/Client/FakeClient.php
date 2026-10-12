<?php

declare(strict_types=1);

namespace Jojomi\Dbl\Tests\Client;

use Jojomi\Dbl\Client\BasicSqlClient;
use Jojomi\Dbl\SqlStyle;
use LogicException;
use PDO;

final class FakeClient extends BasicSqlClient
{
    public int $closedConnections = 0;

    public static function fromEnv(): self
    {
        return new self();
    }

    public function getConnection(): PDO
    {
        throw new LogicException('not used by the fake queries');
    }

    public function closeConnection(): void
    {
        parent::closeConnection();
        $this->closedConnections++;
    }

    public function getSqlStyle(): SqlStyle
    {
        return SqlStyle::Postgres;
    }

    public function enterTransaction(): void
    {
        $this->transactionLevel = 1;
    }
}
