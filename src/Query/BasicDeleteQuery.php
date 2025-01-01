<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Statement\DeleteStatement;

/**
 * BasicDeleteQuery.
 */
class BasicDeleteQuery extends DeleteQuery
{

    private function __construct(private readonly DeleteStatement $statement)
    {
        // NOOP
    }

    public static function fromStatement(DeleteStatement $statement): self
    {
        return new self($statement);
    }

    protected function getQuery(): DeleteStatement {
        return $this->statement;
    }
}