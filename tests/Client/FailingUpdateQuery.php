<?php

declare(strict_types=1);

namespace Jojomi\Dbl\Tests\Client;

use Jojomi\Dbl\Client\Client;
use Jojomi\Dbl\Query\UpdateQuery;
use Throwable;

final class FailingUpdateQuery extends UpdateQuery
{
    public int $calls = 0;

    public function __construct(private readonly Throwable $failure)
    {
    }

    public function execute(Client $client): void
    {
        $this->calls++;

        throw $this->failure;
    }
}
