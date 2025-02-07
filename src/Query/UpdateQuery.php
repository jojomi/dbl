<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client;
use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\NamedParam;
use Jojomi\Dbl\Statement\UpdateStatement;
use Jojomi\Dbl\Statement\Value;
use Stringable;

/**
 * UpdateQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
abstract class UpdateQuery extends BaseQuery
{
    protected UpdateStatement $updateStatement;

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn)->execute();
        } finally {
            $client->closeConnection();
        }
    }

    public function setValue(Field|string $field, Value|NamedParam|string|int|bool|Stringable $value): self
    {
        $this->updateStatement->setValue($field, Value::create($value));

        return $this;
    }

    protected function setTable(string $tableName): self
    {
        $this->updateStatement->setTable($tableName);

        return $this;
    }

    protected function getQuery(): UpdateStatement
    {
        return $this->statement;
    }
}