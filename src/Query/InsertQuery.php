<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use InvalidArgumentException;
use Jojomi\Dbl\Client;
use Stringable;
use function array_flip;
use function array_intersect_key;
use function array_map;
use function implode;
use function sprintf;

/**
 * InsertQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
abstract class InsertQuery extends BaseQuery
{

    protected string $tableName = '';

    /**
     * @var array<array<string, mixed>>
     */
    protected array $rows = [];

    /**
     * @var array<string>
     */
    private array $fields = [];

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn)->execute();
        } finally {
            $client->closeConnection();
        }
    }

    protected function setTable(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    protected function addFields(string ...$name): self
    {
        array_push($this->fields, ...$name);

        return $this;
    }

    /**
     * @param array<string, mixed> $rowData
     */
    protected function addRows(array ...$rowData): self
    {
        foreach ($rowData as $row) {
            $this->rows[] = $row;
        }

        return $this;
    }

    protected function getQuery(): string
    {
        return sprintf(
            'INSERT INTO %s (%s) VALUES %s;',
            $this->tableString($this->tableName),
            implode(', ', array_map(fn (string $s) => $this->columnString($s), $this->fields)),
            $this->getParameters(),
        );
    }

    /**
     * Returns the parameter string for query use.
     */
    protected function getParameters(): string
    {
        $groups = [];
        for ($row = 0; $row < count($this->rows); $row++) {
            $rowData = array_intersect_key($this->rows[$row], array_flip($this->fields));
            if (count($rowData) === 0) {
                continue;
            }
            $f = array_map(static fn (string $field) => ':' . $field . ($row+1), $this->fields);
            $groups[] = '(' . implode(', ', $f) . ')';
        }

        return implode(', ', $groups);
    }

    /**
     * Sets the parameters as specified to the Prepared Statement.
     */
    protected function setParams(): static
    {
        for ($row = 0; $row < count($this->rows); $row++) {
            $rowData = array_intersect_key($this->rows[$row], array_flip($this->fields));
            foreach ($rowData as $field => $value) {
                if (
                    !is_int($value) &&
                    !is_float($value) &&
                    !is_string($value) &&
                    !$value instanceof Stringable &&
                    $value !== null
                ) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The value must be of type float, int, string, Stringable, or null. %s given.',
                            gettype($value),
                        ),
                    );
                }

                $this->addParam($field . ($row+1), $value);
            }
        }

        return $this;
    }

}