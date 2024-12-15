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

    public function beginTransaction(): self
    {
        $this->getConnection()->beginTransaction();
        return $this;
    }

    public function commit(): self
    {
        $this->connection?->commit();
        return $this;
    }

    public function rollBack(): self
    {
        $this->connection?->rollBack();
        return $this;
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
