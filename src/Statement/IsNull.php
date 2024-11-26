<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function sprintf;

/**
 * IS NULL condition.
 */
final readonly class IsNull implements Condition
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

    public function render(): string
    {
        $l = $this->left->getAccessor();

        return sprintf('%s IS NULL', $l);
    }

    public function requiresBrackets(): bool
    {
        return false;
    }

}