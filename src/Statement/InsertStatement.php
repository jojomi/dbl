<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function array_intersect;
use function array_keys;
use function array_map;
use function implode;
use function sprintf;

/**
 * InsertStatement.
 */
final class InsertStatement implements Statement
{
    private ?Table $into = null;

    private bool $ignore = false;

    /** @var array<int, array<string, mixed>> */
    private array $rows = [];

    private function __construct()
    {
        // NOOP
    }

    public static function create(): self
    {
        return new self();
    }

    public function into(Table|string $table): self
    {
        $this->into = Table::create($table);

        return $this;
    }

    public function ignore(bool $value): self
    {
        $this->ignore = $value;

        return $this;
    }

    public function render(bool $omitSemicolon = false): string
    {
        // validate
        $into = $this->into;
        if ($into === null) {
            throw new InvalidStatementException(sprintf('missing into() call on %s', $this::class));
        }
        if (count($this->rows) < 1) {
            throw new InvalidStatementException(sprintf('missing addRow() call on %s', $this::class));
        }

        $s = sprintf(
            'INSERT%s INTO %s (%s) VALUES %s',
            $this->ignore ? ' IGNORE' : '',
            $into->getDefinition(),
            implode(', ', array_map(static fn (Field $field) => $field->getAccessor(), $this->getFields())),
            implode(', ', array_map(fn (array $rowData) => $this->renderRow($rowData), $this->rows)),
        );

        if ($omitSemicolon) {
            return $s;
        }

        return $s . ';';
    }

    /**
     * @param array<string, mixed> $rowData
     */
    public function addRow(array $rowData): self
    {
        $this->rows[] = $rowData;

        return $this;
    }

    /**
     * @return array<\Jojomi\Dbl\Statement\Field>
     */
    public function getFields(): array
    {
        return array_map(static fn (string $fieldName) => Field::create($fieldName), $this->getFieldNames());
    }

    /**
     * @return array<string>
     */
    public function getFieldNames(): array
    {
        if (count($this->rows) === 0) {
            return [];
        }

        $result = array_keys($this->rows[0]);
        for ($i = 1; $i < count($this->rows); $i++) {
            $result = array_intersect($result, array_keys($this->rows[$i]));
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $rowData
     */
    private function renderRow(array $rowData): string
    {
        // order has to match the column name order!
        $values = [];
        foreach ($this->getFieldNames() as $fieldName) {
            $values[$fieldName] = $rowData[$fieldName];
        }

        return sprintf(
            '(%s)',
            implode(', ', array_map(static fn (mixed $value) => Value::create($value)->render(), $values)),
        );
    }

    public function __toString(): string
    {
        return $this->render();
    }
}