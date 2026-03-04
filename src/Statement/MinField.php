<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Jojomi\Dbl\SqlStyle;use Override;use function explode;
use function is_string;
use function sprintf;

/**
 * MinField.
 */
readonly class MinField extends Field
{
    public static function create(string|Field $name, ?string $alias = null, Table|string|null $table = null, bool $raw = false): static
    {
        return parent::create($name, $alias, $table, $raw);
    }

    #[Override]
    public function getNameWithTable(SqlStyle $sqlStyle) : string
    {
        return sprintf('MIN(%s)', parent::getNameWithTable($sqlStyle));
    }
}