<?php

declare(strict_types=1);

namespace Jojomi\Dbl\Tests\Client;

use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BasicSqlClientTest extends TestCase
{
    public function testExecuteRetriesReadAfterLostConnection(): void
    {
        $client = new FakeClient();
        $lost = new RuntimeException('SQLSTATE[HY000]: General error: 7 no connection to the server');
        $query = new FailingSelectQuery([$lost]);

        self::assertSame('ok', $client->execute($query));
        self::assertSame(2, $query->calls);
        self::assertSame(1, $client->closedConnections);
    }

    public function testExecuteDetectsLostConnectionBySqlState(): void
    {
        $pdoException = new PDOException('connection failure');
        $pdoException->errorInfo = ['08006', 7, 'connection failure'];
        $query = new FailingSelectQuery([new RuntimeException('Query failed', previous: $pdoException)]);

        self::assertSame('ok', (new FakeClient())->execute($query));
        self::assertSame(2, $query->calls);
    }

    public function testExecuteRetriesOnlyOnce(): void
    {
        $lost = new RuntimeException('server closed the connection unexpectedly');
        $query = new FailingSelectQuery([$lost, $lost]);

        try {
            (new FakeClient())->execute($query);
            self::fail('expected exception');
        } catch (RuntimeException $e) {
            self::assertSame($lost, $e);
        }
        self::assertSame(2, $query->calls);
    }

    public function testExecuteDoesNotRetryOtherErrors(): void
    {
        $query = new FailingSelectQuery([new RuntimeException('SQLSTATE[42P01]: relation does not exist')]);

        $this->expectException(RuntimeException::class);
        try {
            (new FakeClient())->execute($query);
        } finally {
            self::assertSame(1, $query->calls);
        }
    }

    public function testExecuteDoesNotRetryWrites(): void
    {
        $query = new FailingUpdateQuery(new RuntimeException('no connection to the server'));

        $this->expectException(RuntimeException::class);
        try {
            (new FakeClient())->execute($query);
        } finally {
            self::assertSame(1, $query->calls);
        }
    }

    public function testExecuteDoesNotRetryInsideTransaction(): void
    {
        $client = new FakeClient();
        $client->enterTransaction();
        $query = new FailingSelectQuery([new RuntimeException('no connection to the server')]);

        $this->expectException(RuntimeException::class);
        try {
            $client->execute($query);
        } finally {
            self::assertSame(1, $query->calls);
        }
    }
}
