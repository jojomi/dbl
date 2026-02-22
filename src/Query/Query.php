<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use Jojomi\Dbl\Client\Client;

/**
 * Query.
 *
 * @template T
 */
interface Query
{

    /**
     * @return T
     */
    public function execute(Client $client);

    public function __toString(): string;

}