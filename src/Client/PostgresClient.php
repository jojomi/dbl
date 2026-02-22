<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use Jojomi\Dbl\SqlStyle;use PDO;use PDOException;use RuntimeException;use function getenv;use function sprintf;

final class PostgresClient extends BasicSqlClient
{
    public function __construct(
        private readonly PostgresCredentials $credentials,
        private readonly ?int $port = null,
    ) {}

    public static function fromEnv(): self
    {
        $port = getenv('POSTGRES_PORT');

        return new self(
            PostgresCredentials::fromEnv(),
            $port === false ? null : (int) $port
        );
    }

    public function getConnection(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->credentials->hostname,
            $this->port ?? 5432,
            $this->credentials->database,
        );

        try {
            $this->connection = new PDO(
                $dsn,
                $this->credentials->username,
                $this->credentials->password,
                [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Could not connect to database: ' . $e->getMessage(), previous: $e);
        }

        return $this->connection;
    }

    public function getSqlStyle(): SqlStyle
    {
        return SqlStyle::Postgres;
    }
}
