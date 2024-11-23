<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use App\Data\Arry;
use App\Exception\AppException;
use Jojomi\Dbl\Client;
use PDO;
use PDOException;
use RuntimeException;
use function sprintf;

/**
 * SelectListQuery.
 *
 * @template T
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<\App\Data\Lst<T>>
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
     * @phpstan-return array<T>
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
            throw new AppException(
                sprintf('%s: %s', static::class, $x->getMessage()), context: [
                'params' => $this->params,
                'query' => $this->getQuery(),
                ], previous: $x,
            );
        } finally {
            $client->closeConnection();
        }

        return $result;
    }
}