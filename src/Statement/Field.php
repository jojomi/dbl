<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use function sprintf;

/**
 * Field.
 */
readonly class Field
{

    private function __construct(private string $name, private ?string $alias, private ?Table $table, private bool $raw)
    {
        // NOOP
    }

    public static function create(string|self $name, ?string $alias = null, Table|string|null $table = null, bool $raw = false): self
    {
        if (!is_string($name)) {
            return $name;
        }
        if (is_string($table)) {
            $table = Table::create($table);
        }

        return new self($name, alias: $alias, table: $table, raw: $raw);
    }

    public function getDefinition(): string
    {
        $name = $this->getName();

        if ($this->table !== null && $this->raw === false) {
            $name = $this->table->getPrefix() . '.' . $name;
        }

        if ($this->alias === null) {
            return sprintf('%s', $name);
        }

        return sprintf("%s AS '%s'", $name, $this->alias);
    }

    public function getAccessor(): string
    {
        if ($this->raw && $this->alias === null) {
            throw new InvalidArgumentException(sprintf('%s is accessed without alias', $this->name));
        }

        $tableString = '';
        $table = $this->table;
        if ($table !== null) {
            $tableString = $table->getPrefix() . '.';
        }

        return sprintf('%s%s', $tableString, $this->alias !== null ? $this->escape($this->alias) : $this->getName());
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
        return new self(name: $this->name, alias: $this->alias, table: $table, raw: $this->raw);
    }

    private function getName(): string
    {
        if ($this->raw) {
            return $this->name;
        }

        return $this->escape($this->name);
    }

    private function escape(string $input): string
    {
        return sprintf('`%s`', $input);
    }

}