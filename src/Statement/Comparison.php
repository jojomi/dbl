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
        if ($this->right === null && !in_array($this->comparisonType, [ComparisonType::equal, ComparisonType::unequal])) {
            throw new InvalidStatementException(sprintf('Cannot compare to null with %s', $this->comparisonType->value));
        }
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
        $comparison = $this->comparisonType->value;
        $right = Value::create($this->right);
        if ($this->right === null) {
            $comparison = match ($this->comparisonType) {
                ComparisonType::equal => 'IS',
                ComparisonType::unequal => 'IS NOT',
                default => throw new InvalidStatementException(sprintf('Invalid comparison type for null value: %s', $this->comparisonType->value)),
            };
        }

        return trim(
            sprintf(
                '%s %s %s',
                $this->left->getAccessor(),
                $comparison,
                $right->render(),
            ),
        );
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}