<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use Jojomi\Dbl\SqlStyle;use PDO;use PDOException;use RuntimeException;use function getenv;use function sprintf;

final class PostgresClient extends BasicSqlClient
{
    /**
     * @param string|null $timeZone session time zone, e.g. date_default_timezone_get(): date parameters are bound
     *                              without offset and interpreted in this zone; null keeps the server's default
     */
    public function __construct(
        private readonly PostgresCredentials $credentials,
        private readonly ?int $port = null,
        private readonly ?string $timeZone = null,
    ) {}

    public static function fromEnv(?string $timeZone = null): self
    {
        $port = getenv('POSTGRES_PORT');

        return new self(
            PostgresCredentials::fromEnv(),
            $port === false ? null : (int) $port,
            $timeZone,
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
            throw new RuntimeException(sprintf('Could not connect to database (DSN: %s): %s', $dsn, $e->getMessage()), previous: $e);
        }

        if ($this->timeZone !== null) {
            $this->connection->exec(sprintf('SET TIME ZONE %s', $this->connection->quote($this->timeZone)));
        }

        return $this->connection;
    }

    public function getSqlStyle(): SqlStyle
    {
        return SqlStyle::Postgres;
    }
}
