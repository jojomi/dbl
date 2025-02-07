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

    public static function insert(): InsertStatement
    {
        return InsertStatement::create();
    }

    public static function update(Table|string $table): UpdateStatement
    {
        return UpdateStatement::create()->setTable($table);
    }
}