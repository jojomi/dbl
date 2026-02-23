<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Jojomi\Dbl\SqlStyle;use function explode;
use function is_string;
use function sprintf;

/**
 * Field.
 */
readonly class Field
{

    protected function __construct(protected string $name, protected ?string $alias, protected ?Table $table, protected bool $raw)
    {
        // NOOP
    }

    public static function create(string|self $name, ?string $alias = null, Table|string|null $table = null, bool $raw = false): static
    {
        if (!is_string($name)) {
            return $name;
        }

        if (!$raw && str_contains($name, '.')) {
            $table = explode('.', $name)[0];
            $name = explode('.', $name)[1];
        }
        if (is_string($table)) {
            $table = Table::create($table);
        }

        return new static($name, alias: $alias, table: $table, raw: $raw);
    }

    public function getDefinition(SqlStyle $sqlStyle): string
    {
        $name = $this->getName($sqlStyle);

        if ($this->table !== null && $this->raw === false) {
            $name = $this->table->getPrefix($sqlStyle) . '.' . $name;
        }

        if ($this->alias === null) {
            return sprintf('%s', $name);
        }

        return sprintf("%s AS %s", $name, Escaper::fieldAlias($this->alias, $sqlStyle));
    }

    public function getAccessor(SqlStyle $sqlStyle): string
    {
        if ($this->raw && $this->alias === null) {
            throw new InvalidArgumentException(sprintf('%s is accessed without alias', $this->name));
        }

        $tableString = '';
        $table = $this->table;
        if ($table !== null) {
            $tableString = $table->getPrefix($sqlStyle) . '.';
        }

        return sprintf('%s%s', $tableString, $this->alias !== null ? Escaper::fieldAlias($this->alias, $sqlStyle) : $this->getName($sqlStyle));
    }

    public function getRawName(): string
    {
        return $this->name;
    }

    public function getTable(): ?Table
    {
        return $this->table;
    }

    public function withTable(Table $table): self
    {
        return new self(name: $this->name, alias: $this->alias, table: $this->table ?? $table, raw: $this->raw);
    }

    protected function getName(SqlStyle $sqlStyle): string
    {
        if ($this->raw) {
            return $this->name;
        }

        return Escaper::fieldName($this->name, $sqlStyle);
    }
}