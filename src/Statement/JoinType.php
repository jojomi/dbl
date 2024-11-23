<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * JoinType.
 */
enum JoinType: string
{
    case inner = 'INNER JOIN';
    case left = 'LEFT JOIN';
    case right = 'RIGHT JOIN';
}