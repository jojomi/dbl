<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;
use Jojomi\Dbl\Statement\DeleteStatement;

/**
 * DeleteQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
class DeleteQuery extends BaseQuery
{
    private function __construct(protected readonly DeleteStatement $statement)
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

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn, $client->getSqlStyle())->execute();
        } finally {
            $client->closeConnection();
        }
    }
}