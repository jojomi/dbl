<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Webmozart\Assert\Assert;
use function is_string;
use function sprintf;

/**
 * Order.
 */
final readonly class Join
{
    private function __construct(private JoinType $type, private SelectStatement|Field $source, private Condition $condition, private ?Table $targetTable = null, private ?string $alias = null)
    {
        // NOOP
    }

    public static function bySubquery(JoinType $joinType, SelectStatement $sub, string $alias, Condition $condition): self
    {
        return new self(type: $joinType, source: $sub, condition: $condition, alias: $alias);
    }

    public static function byField(JoinType $joinType, string|Field $fieldSource, string|Field|Table $target): self
    {
        if (is_string($fieldSource)) {
            $fieldSource = Field::create($fieldSource);
        }
        $fieldTarget = match (true) {
            is_string($target) => Field::create($target),
            $target instanceof Table => Field::create('id', table: $target),
            $target instanceof Field => $target,
        };
        $targetTable = $fieldTarget->getTable();
        if ($targetTable === null) {
            throw new InvalidArgumentException(
                'can not join with incomplete target field: ' . $fieldTarget->getAccessor(),
            );
        }

        $condition = Comparison::of($fieldSource, ComparisonType::equal, $fieldTarget);

        return new self(type: $joinType, source: $fieldSource, condition: $condition, targetTable: $targetTable);
    }

    /**
     * Does not override if set already.
     */
    public function withSourceTable(Table $table): self
    {
        $source = $this->source;
        if ($source instanceof Field && $source->getTable() !== null) {
            $source = $source->withTable($table);
        }

        return new self(
            type: $this->type,
            source: $source,
            condition: $this->condition->withTable($table),
            targetTable: $this->targetTable,
            alias: $this->alias,
        );
    }

    public function getTargetTable(): ?Table
    {
        return $this->targetTable;
    }

    public function render(): string
    {
        if ($this->source instanceof Field) {
            $targetTable = $this->targetTable;
            Assert::notNull($targetTable);

            return sprintf(
                '%s %s ON %s',
                $this->type->value,
                $targetTable->getDefinition(),
                $this->condition->render(),
            );
        }

        return sprintf(
            '%s (%s) `%s` ON %s',
            $this->type->value,
            $this->source->render(omitSemicolon: true),
            $this->alias,
            $this->condition->render(),
        );
    }

    public function __toString(): string
    {
        return $this->render();
    }

}