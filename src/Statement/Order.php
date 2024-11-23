<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * Order.
 */
final readonly class Order
{

    private function __construct(private Field $field, private OrderType $type)
    {
        // NOOP
    }

    public static function create(string|Field $field, OrderType $type): self
    {
        if (is_string($field)) {
            $field = Field::create($field);
        }

        return new self($field, type: $type);
    }

    public function render(): string
    {
        return $this->field->getAccessor() . ' ' . $this->type->value;
    }

    public function withTable(Table $table): self
    {
        return self::create(
            field: $this->field->withTable($table),
            type: $this->type,
        );
    }

    public function getTable(): ?Table
    {
        return $this->field->getTable();
    }

    public function __toString(): string
    {
        return $this->render();
    }

}