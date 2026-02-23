<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Jojomi\Dbl\SqlStyle;use function explode;
use function is_string;
use function sprintf;

/**
 * CountField.
 */
readonly class CountField extends Field
{
    public static function create(string|Field $name, ?string $alias = null, Table|string|null $table = null, bool $raw = false): static
    {
        return parent::create($name, $alias, $table, $raw);
    }

    protected function getName(SqlStyle $sqlStyle) : string
    {
        return sprintf('COUNT(%s)', Escaper::fieldName(parent::getName($sqlStyle), $sqlStyle));
    }
}