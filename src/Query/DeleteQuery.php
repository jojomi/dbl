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
abstract class DeleteQuery extends BaseQuery
{

    abstract protected function getQuery(): DeleteStatement;

    public function execute(Client $client): void
    {
        $conn = $client->getConnection();
        try {
            $this->getPreparedStatement($conn)->execute();
        } finally {
            $client->closeConnection();
        }
    }
}