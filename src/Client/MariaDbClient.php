<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use PDO;use PDOException;use RuntimeException;use function getenv;use function sprintf;

/**
 * MariaDbClient.
 */
final class MariaDbClient extends BasicSqlClient
{
    public function __construct(private readonly MariaDbCredentials $credentials, private readonly ?int $port = null)
    {
        // NOOP
    }

    public static function fromEnv(): self
    {
        $port = getenv('MARIADB_PORT');

        return new self(MariaDbCredentials::fromEnv(), $port === false ? null : (int)$port);
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
}
