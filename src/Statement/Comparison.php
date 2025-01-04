<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Stringable;
use function sprintf;

/**
 * Comparison.
 */
final readonly class Comparison implements Condition
{
    private function __construct(private Field $left, private ComparisonType $comparisonType, private string|int|Field|NamedParam|Stringable|null $right)
    {
        // NOOP
    }

    public static function of(Field|string $left, ComparisonType $comparisonType, string|int|Field|NamedParam|Stringable|null $right = null): self
    {
        return new self(Field::create($left), $comparisonType, $right);
    }

    public function withTable(Table $table): static
    {
        return self::of(
            left: $this->left->withTable($table),
            comparisonType: $this->comparisonType,
            right: $this->right,
        );
    }

    public function render(): string
    {
        return trim(sprintf('%s %s %s', $this->left->getAccessor(), $this->comparisonType->value, Value::create($this->right)->render()));
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}