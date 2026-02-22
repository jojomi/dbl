<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;use Stringable;

/**
 * Equality check.
 */
final readonly class Eq implements Condition
{
    private function __construct(private Field $left, private string|int|Field|NamedParam|Stringable|null $right)
    {
        // NOOP
    }

    public static function of(Field|string $left, string|int|Field|NamedParam|Stringable|null $right = null): self
    {
        return new self(Field::create($left), $right);
    }

    public function withTable(Table $table): static
    {
        return self::of(
            left: $this->left->withTable($table),
            right: $this->right,
        );
    }

    public function render(SqlStyle $sqlStyle): string
    {
        return Comparison::of($this->left, ComparisonType::equal, $this->right)->render($sqlStyle);
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}