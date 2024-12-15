<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * StatementBuilder.
 */
final class StatementBuilder
{
    private function __construct()
    {
        // NOOP
    }

    public static function select(): SelectStatement
    {
        return SelectStatement::create();
    }

    public static function delete(): DeleteStatement
    {
        return DeleteStatement::create();
    }
}