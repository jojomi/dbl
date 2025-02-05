<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * NOT IN condition.
 */
final readonly class NotIn extends In
{
    protected function getTemplate(): string
    {
        return '%s NOT IN (%s)';
    }
}