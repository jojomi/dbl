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

    /**
     * $type is ignored if $field is of type Order already.
     */
    public static function create(string|Field|self $field, OrderType $type = OrderType::ascending): self
    {
        if ($field instanceof self) {
            return $field;
        }

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