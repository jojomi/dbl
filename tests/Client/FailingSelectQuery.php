<?php

declare(strict_types=1);

namespace Jojomi\Dbl\Tests\Client;

use Jojomi\Dbl\Client\Client;
use Jojomi\Dbl\Query\SelectSingleQuery;
use Throwable;

/**
 * @extends \Jojomi\Dbl\Query\SelectSingleQuery<string>
 */
final class FailingSelectQuery extends SelectSingleQuery
{
    public int $calls = 0;

    /**
     * @param list<\Throwable> $failures thrown by the first executions, in order
     */
    public function __construct(private array $failures)
    {
    }

    public function execute(Client $client): mixed
    {
        $this->calls++;
        $failure = array_shift($this->failures);
        if ($failure !== null) {
            throw $failure;
        }

        return 'ok';
    }

    protected function getQuery(): string
    {
        return 'SELECT 1';
    }

    protected function parseRow(array $rowData, Client $client): string
    {
        return 'ok';
    }
}
