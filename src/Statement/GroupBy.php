<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * GroupBy.
 */
final readonly class GroupBy
{

    private function __construct(private Field $field)
    {
        // NOOP
    }

    public static function create(string|Field $field): self
    {
        if (is_string($field)) {
            $field = Field::create($field);
        }

        return new self($field);
    }

    public function render(): string
    {
        return $this->field->getAccessor();
    }

    public function getTable(): ?Table
    {
        return $this->field->getTable();
    }

    public function withTable(Table $table): self
    {
        return self::create(
            field: $this->field->withTable($table),
        );
    }

    public function __toString(): string
    {
        return $this->render();
    }

}