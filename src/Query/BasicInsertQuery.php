<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Statement\InsertStatement;

/**
 * BasicInsertQuery.
 */
class BasicInsertQuery extends InsertQuery
{

    private function __construct(private readonly InsertStatement $statement)
    {
        // NOOP
    }

    public static function fromStatement(InsertStatement $statement): self
    {
        return new self($statement);
    }

    public function isNoOp(): bool
    {
        return false;
    }

    protected function getQuery(): string
    {
        return $this->statement->render();
    }
}