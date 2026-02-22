<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;use Jojomi\Dbl\Statement\Condition;use Jojomi\Dbl\Statement\Field;use Jojomi\Dbl\Statement\NamedParam;use Jojomi\Dbl\Statement\UpdateStatement;use Jojomi\Dbl\Statement\Value;use Stringable;

/**
 * UpdateQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
abstract class UpdateQuery extends BaseQuery
{
    protected UpdateStatement $statement;

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn)->execute();
        } finally {
            $client->closeConnection();
        }
    }

    public function setValue(Field|string $field, Value|NamedParam|string|int|bool|Stringable $value): static
    {
        $this->statement->setValue($field, Value::create($value));

        return $this;
    }

    protected function setTable(string $tableName): static
    {
        $this->statement->setTable($tableName);

        return $this;
    }

    protected function where(Condition $condition): static
    {
        $this->statement->where($condition);

        return $this;
    }

    protected function getQuery(): UpdateStatement
    {
        return $this->statement;
    }
}