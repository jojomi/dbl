<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;use function sprintf;

/**
 * IS NOT NULL condition.
 */
final readonly class IsNotNull implements Condition
{
    private function __construct(private Field $left)
    {
        // NOOP
    }

    public static function of(Field|string $left): self
    {
        return new self(Field::create($left));
    }

    public function withTable(Table $table): static
    {
        if ($this->left->getTable() !== null) {
            return $this;
        }

        return self::of(
            left: $this->left->withTable($table),
        );
    }

    public function render(SqlStyle $sqlStyle): string
    {
        $l = $this->left->getAccessor($sqlStyle);

        return sprintf('%s IS NOT NULL', $l);
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}