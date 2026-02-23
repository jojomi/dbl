<?php

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;/**
 * Escape different types of identifers.
 */
class Escaper {
    public static function tableName(string $tableName, SqlStyle $sqlStyle): string
    {
        return match ($sqlStyle) {
            SqlStyle::MariaDb => '`' . $tableName . '`',
            sqlStyle::Postgres => '"' . $tableName . '"',
        };
    }

    public static function tableAlias(string $alias, SqlStyle $sqlStyle): string
    {
        return match ($sqlStyle) {
            SqlStyle::MariaDb => '`' . $alias . '`',
            sqlStyle::Postgres => '"' . $alias . '"',
        };
    }

    public static function fieldAlias(string $alias, SqlStyle $sqlStyle): string
    {
        return self::tableAlias($alias, $sqlStyle);
    }

    public static function joinAlias(string $alias, SqlStyle $sqlStyle): string
    {
        return self::tableAlias($alias, $sqlStyle);
    }

    public static function fieldName(string $name, SqlStyle $sqlStyle): string
    {
        return self::tableAlias($name, $sqlStyle);
    }
}