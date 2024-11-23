<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * OrderType.
 */
enum OrderType: string
{
    case ascending = 'ASC';
    case descending = 'DESC';
}