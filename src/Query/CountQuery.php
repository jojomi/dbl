<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client;
use Jojomi\Dbl\Statement\Field;
use Jojomi\Dbl\Statement\Statement;
use Jojomi\Dbl\Statement\StatementBuilder;
use Jojomi\Dbl\Statement\Table;
use Jojomi\Typer\Arry;

/**
 * CountQuery.
 *
 * @extends \Jojomi\Dbl\Query\SelectSingleQuery<0|positive-int>
 */
abstract class CountQuery extends SelectSingleQuery
{

    abstract protected function getTable(): Table|string;

    final private function __construct()
    {
        // NOOP
    }

    public static function create(): static
    {
        return new static();
    }

    protected function getQuery(): Statement
    {
        return StatementBuilder::select()
            ->from($this->getTable())
            ->fields(Field::create('COUNT(*)', 'count', raw: true));
    }

    /**
     * @param array<string, mixed> $rowData
     */
    protected function parseRow(array $rowData, Client $client): int
    {
        return Arry::getRequiredNonNegativeInt($rowData, 'count');
    }

}