<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Client;

use function getenv;

/**
 * PostgresCredentials.
 */
final readonly class PostgresCredentials
{

    private function __construct(public string $hostname, public string $username, public string $password, public string $database)
    {
        // NOOP
    }

    public static function create(string $hostname, string $username, string $password, string $database): self
    {
        return new self($hostname, $username, $password, $database);
    }

    public static function fromEnv(): self
    {
        $hostname = getenv('POSTGRES_HOSTNAME');
        if ($hostname === false || $hostname === '') {
            $hostname = '127.0.0.1';
        }

        $user = getenv('POSTGRES_USER');
        if ($user === false || $user === '') {
            $user = '';
        }

        $password = getenv('POSTGRES_PASSWORD');
        if ($password === false || $password === '') {
            $password = '';
        }

        $database = getenv('POSTGRES_DATABASE');
        if ($database === false || $database === '') {
            $database = '';
        }

        return self::create($hostname, $user, $password, $database);
    }

}