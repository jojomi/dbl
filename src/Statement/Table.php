<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;
use function sprintf;

/**
 * Table.
 */
final readonly class Table
{

    private function __construct(private string $name, private ?string $alias, private bool $raw)
    {
        // NOOP
    }

    public static function create(self|string $name, ?string $alias = null, bool $raw = false): self
    {
        if (!is_string($name)) {
            return $name;
        }

        return new self($name, $alias, $raw);
    }

    public function getDefinition(SqlStyle $sqlStyle): string
    {
        $name = $this->getName($sqlStyle);
        if ($this->alias === null) {
            return $name;
        }

        return sprintf('%s %s', $name, Escaper::tableAlias($this->alias, $sqlStyle)); // AS is optional in Postgres
    }

    public function getPrefix(SqlStyle $sqlStyle): string
    {
        return sprintf('%s', $this->alias !== null ? Escaper::tableAlias($this->alias, $sqlStyle) : $this->getName($sqlStyle));
    }

    private function getName(SqlStyle $sqlStyle): string
    {
        if ($this->raw) {
            return $this->name;
        }

        return Escaper::tableName($this->name, $sqlStyle);
    }
}