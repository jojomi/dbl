<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;use Jojomi\Typer\Arry;use PDO;use PDOException;use RuntimeException;use function sprintf;

/**
 * SelectListQuery.
 *
 * @template T
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<list<T>>
 */
abstract class SelectListQuery extends BaseQuery
{

    /**
     * @param array<string, mixed> $rowData
     *
     * @return T
     */
    abstract protected function parseRow(array $rowData, Client $client);

    /**
     * @phpstan-return list<T>
     */
    public function executeRaw(Client $client): array
    {
        $conn = $client->getConnection();
        $result = [];
        try {
            $stmt = $this->getPreparedStatement($conn);
            $executionResult = $stmt->execute();
            if ($executionResult === false) {
                throw new RuntimeException('Failed to get data');
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $element = $this->parseRow(Arry::asStringMap($row), $client);
                $result[] = $element;
            }
        } catch (PDOException $x) {
            throw new RuntimeException(sprintf(
                '%s: %s, query: %s, params %s',
                static::class,
                $x->getMessage(),
                $this->getQuery(),
                json_encode($this->params) ?: '?',
            ));
        } finally {
            $client->closeConnection();
        }

        return $result;
    }
}