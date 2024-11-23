<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function sprintf;

/**
 * Comparison.
 */
final readonly class Comparison implements Condition
{
    private function __construct(private Field $left, private ComparisonType $comparisonType, private string|int|Field|NamedParam|null $right)
    {
        // NOOP
    }

    public static function of(Field|string $left, ComparisonType $comparisonType, string|int|Field|NamedParam|null $right = null): self
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
        $l = $this->left->getAccessor();

        $right = $this->right;
        $r = match (true) {
            is_string($right) => "'{$right}'",
            is_int($right) || $right instanceof NamedParam => (string)$right,
            $right instanceof Field => $right->getAccessor(),
            $right === null => '',
        };

        return trim(sprintf('%s %s %s', $l, $this->comparisonType->value, $r));
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}