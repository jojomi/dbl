<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function is_string;
use function sprintf;

/**
 * IN condition.
 */
final readonly class In implements Condition
{
    /**
     * @param array<string|int|\Jojomi\Dbl\Statement\NamedParam> $values
     */
    private function __construct(private Field $left, private array $values)
    {
        // NOOP
    }

    /**
     * @param array<string|int|\Jojomi\Dbl\Statement\NamedParam> $values
     */
    public static function create(Field|string $left, array $values): self
    {
        return new self(Field::create($left), $values);
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
        $l = $this->left->getAccessor();

        $values = array_map(
            static fn (string|int|NamedParam $right) => match (true) {
            is_string($right) => "'{$right}'",
            default => (string)$right,
            }, $this->values,
        );

        return sprintf('%s IN (%s)', $l, implode(', ', $values));
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}