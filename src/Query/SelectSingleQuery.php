<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client;
use Jojomi\Typer\Arry;
use PDO;
use PDOException;
use RuntimeException;
use function json_encode;
use function sprintf;

/**
 * SelectSingleQuery.
 *
 * @template T
 *
 * @extends \Jojomi\Dbl\Query\SelectQuery<?T, T>
 */
abstract class SelectSingleQuery extends SelectQuery
{

    /**
     * @phpstan-return ?T
     */
    public function execute(Client $client): mixed
    {
        $conn = $client->getConnection();
        try {
            $stmt = $this->getPreparedStatement($conn);
            $executionResult = $stmt->execute();
            if ($executionResult === false) {
                throw new RuntimeException('Failed to get data');
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($rows) === 0) {
                return null;
            }

            return $this->parseRow(Arry::asStringMap($rows[0]), $client);
        } catch (PDOException $x) {
            throw new RuntimeException(sprintf(
                '%s: %s (query: %s, params: %s)',
                static::class,
                $x->getMessage(),
                $this->getQuery(),
                json_encode($this->params) ?: '?',
            ));
        } finally {
            $client->closeConnection();
        }
    }

}