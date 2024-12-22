<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client;
use Jojomi\Dbl\Statement\DeleteStatement;
use Jojomi\Dbl\Statement\StatementBuilder;

/**
 * DeleteQuery.
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<void>
 */
abstract class DeleteQuery extends BaseQuery
{

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn)->execute();
        } finally {
            $client->closeConnection();
        }
    }

    protected function getQuery(): DeleteStatement
    {
        return StatementBuilder::delete();
    }
}