<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Stringable;
use function array_map;
use function implode;
use function sprintf;

/**
 * IN condition.
 */
readonly class In implements Condition
{
    /**
     * @param array<\Jojomi\Dbl\Statement\Value> $values
     */
    private function __construct(private Field $left, private array $values)
    {
        // NOOP
    }

    public function withTable(Table $table): static
    {
        if ($this->left->getTable() !== null) {
            return $this;
        }

        return self::create(
            left: $this->left->withTable($table),
            values: $this->values,
        );
    }

    public function render(): string
    {
        $values = array_map(
            static fn (Value $right) => $right->render(), $this->values,
        );

        return sprintf($this->getTemplate(), $this->left->getAccessor(), implode(', ', $values));
    }

    public function requiresBrackets(): bool
    {
        return false;
    }

    /**
     * @param array<string|int|bool|\Jojomi\Dbl\Statement\Field|\Jojomi\Dbl\Statement\NamedParam|\Stringable|\Jojomi\Dbl\Statement\Value> $values
     */
    public static function create(Field|string $left, array $values): static
    {
        /** @phpstan-ignore-next-line */
        return new static(
            Field::create($left),
            array_map(static fn (string|int|bool|Field|NamedParam|Stringable|Value $v) => Value::create($v), $values),
        );
    }

    protected function getTemplate(): string
    {
        return '%s IN (%s)';
    }
}