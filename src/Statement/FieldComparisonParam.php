<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * FieldCompareParam.
 */
final readonly class FieldComparisonParam implements Condition
{
    private function __construct(private Field|string $field, private ComparisonType $comparisonType, private NamedParam|string $param)
    {
        // NOOP
    }

    public static function create(Field|string $field, ComparisonType $comparisonType, NamedParam|string $param): self
    {
        return new self($field, $comparisonType, $param);
    }

    public function withTable(Table $table): static
    {
        return self::create(field: $this->field, comparisonType: $this->comparisonType, param: $this->param);
    }

    public function render(): string
    {
        return Comparison::of(
            Field::create($this->field),
            $this->comparisonType,
            NamedParam::create($this->param),
        )->render();
    }

    public function requiresBrackets(): bool
    {
        return false;
    }
}