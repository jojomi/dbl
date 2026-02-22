<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;

/**
 * SelectQuery.
 *
 * @template ReturnType
 * @template SingleType
 *
 * @extends \Jojomi\Dbl\Query\BaseQuery<ReturnType>
 */
abstract class SelectQuery extends BaseQuery
{

    /**
     * @param array<string, mixed> $rowData
     *
     * @return SingleType
     */
    abstract protected function parseRow(array $rowData, Client $client);

}