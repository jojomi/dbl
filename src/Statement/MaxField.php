<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;
use Override;
use function sprintf;

/**
 * MaxField.
 */
readonly class MaxField extends Field
{
    public static function create(string|Field $name, ?string $alias = null, Table|string|null $table = null, bool $raw = false): static
    {
        return parent::create($name, $alias, $table, $raw);
    }


    #[Override]
    public function getNameWithTable(SqlStyle $sqlStyle) : string
    {
        return sprintf('MAX(%s)', parent::getNameWithTable($sqlStyle));
    }
}