<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;use Jojomi\Dbl\Statement\InsertStatement;

/**
 * InsertQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
class InsertQuery extends BaseQuery
{
    private function __construct(protected readonly InsertStatement $statement) {}

    public static function fromStatement(InsertStatement $statement): self
    {
        return new self($statement);
    }

    public function execute(Client $client): void
    {
        // no rows? -> NO-OP!
        if ($this->statement->isNoOp()) {
            return;
        }

        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn, $client->getSqlStyle())->execute();
        } finally {
            $client->closeConnection();
        }
    }

    protected function getQuery(): InsertStatement
    {
        return $this->statement;
    }
}