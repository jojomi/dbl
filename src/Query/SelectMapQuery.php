<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client;
use Jojomi\Typer\Arry;
use PDO;
use PDOException;
use RuntimeException;
use Webmozart\Assert\Assert;
use function json_encode;
use function sprintf;

/**
 * SelectMapQuery.
 *
 * @template T
 *
 * @extends \Jojomi\Dbl\Query\SelectQuery<array<T>, T>
 */
abstract class SelectMapQuery extends SelectQuery
{

    /**
     * @param array<string, mixed> $rowData
     */
    abstract protected function getKey(array $rowData): string|int|null;

    /**
     * @phpstan-return array<array-key, T>
     */
    public function execute(Client $client): array
    {
        $conn = $client->getConnection();
        /** @var array<array-key, T> $result */
        $result = [];
        try {
            $stmt = $this->getPreparedStatement($conn);
            $executionResult = $stmt->execute();
            if ($executionResult === false) {
                throw new RuntimeException('Failed to get data');
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Assert::isArray($rows);

            foreach ($rows as $row) {
                $row = Arry::asStringMap($row);
                $this->addRowToResult($result, $row, $client);
            }
        } catch (PDOException $x) {
            throw new RuntimeException(sprintf(
                '%s: %s (query: %s, params: %s)',
                static::class,
                $x->getMessage(),
                $this->getQuery(),
                json_encode($this->params) ?: '?',
            ), previous: $x);
        } finally {
            $client->closeConnection();
        }

        return $result;
    }

    /**
     * @param array<array-key, T> $result
     * @param array<string, mixed> $row
     */
    protected function addRowToResult(array &$result, array $row, Client $client): void
    {
        $element = $this->parseRow($row, $client);
        $key = $this->getKey($row);
        if ($key === null) {
            $result[] = $element;
        } else {
            $result[$key] = $element;
        }
    }
}