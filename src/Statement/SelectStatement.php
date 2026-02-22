<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;use function array_map;
use function DeepCopy\deep_copy;
use function implode;
use function is_string;

/**
 * SelectStatement.
 */
final class SelectStatement extends BaseStatement
{
    /**
     * @var array<\Jojomi\Dbl\Statement\Table> $from
     */
    private array $from = [];

    private bool $distinct = false;

    /**
     * @var array<\Jojomi\Dbl\Statement\Field> $fields
     */
    private array $fields = [];

    /**
     * @var array<\Jojomi\Dbl\Statement\Order> $orderBys
     */
    private array $orderBys = [];

    /**
     * @var array<\Jojomi\Dbl\Statement\GroupBy> $groupBys
     */
    private array $groupBys = [];

    /**
     * @var array<\Jojomi\Dbl\Statement\Join> $joins
     */
    private array $joins = [];

    private ?int $limit = null;

    private ?int $offset = null;

    private ?Condition $condition = null;

    private ?Table $currentTable = null;

    private function __construct()
    {
        // NOOP
    }

    public static function create(): self
    {
        return new self();
    }

    public function distinct(bool $distinct = true): self
    {
        $this->distinct = $distinct;

        return $this;
    }

    public function setCurrentTable(Table|string|null $currentTable): self
    {
        $this->currentTable = $currentTable === null ? null : Table::create($currentTable);

        return $this;
    }

    public function resetCurrentTable(): self
    {
        $this->setCurrentTable(null);

        return $this;
    }

    public function from(Table|string $table): self
    {
        if (is_string($table)) {
            $table = Table::create($table);
        }

        $this->from[] = $table;

        return $this;
    }

    public function fromLocked(Table|string $table): self
    {
        $this->from($table);
        $this->setCurrentTable(Table::create($table));

        return $this;
    }

    public function fields(Field|string ...$field): self
    {
        foreach ($field as $f) {
            if (is_string($f)) {
                $f = Field::create($f);
            }

            if ($f->getTable() === null) {
                $currentTable = $this->currentTable;
                if ($currentTable !== null) {
                    $f = $f->withTable($currentTable);
                }
            }

            $this->fields[] = $f;
        }

        return $this;
    }

    public function where(Condition $condition): self
    {
        if ($this->currentTable !== null) {
            $condition = $condition->withTable($this->currentTable);
        }

        $this->condition = $this->condition !== null ? AndCondition::create($this->condition, $condition) : $condition;

        return $this;
    }

    public function orderBy(Order|Field|string ...$order): self
    {
        foreach ($order as $o) {
            $o = Order::create($o);

            $currentTable = $this->currentTable;
            if ($currentTable !== null && $o->getTable() === null) {
                $o = $o->withTable($currentTable);
            }

            $this->orderBys[] = $o;
        }

        return $this;
    }

    public function limit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(?int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function join(Join $join): self
    {
        $currentTable = $this->currentTable;
        if ($currentTable !== null) {
            $join = $join->withSourceTable($currentTable);
        }

        $this->joins[] = $join;

        return $this;
    }

    public function joinLocked(Join $join): self
    {
        $this->join($join);

        $targetTable = $join->getTargetTable();
        if ($targetTable !== null) {
            $this->setCurrentTable($targetTable);
        }

        return $this;
    }

    public function groupBy(GroupBy|string ...$groupBy): self
    {
        $currentTable = $this->currentTable;
        foreach ($groupBy as $g) {
            if (is_string($g)) {
                $g = GroupBy::create($g);
            }
            if ($currentTable !== null && $g->getTable() === null) {
                $g = $g->withTable($currentTable);
            }

            $this->groupBys[] = $g;
        }

        return $this;
    }

    public function render(?SqlStyle $sqlStyle = null, bool $omitSemicolon = false): string
    {
        $sqlStyle ??= $this->getRenderStyle();

        $s = 'SELECT ';
        if ($this->distinct) {
            $s .= 'DISTINCT ';
        }
        $s .= implode(', ', array_map(static fn (Field $f) => $f->getDefinition($sqlStyle), $this->fields));
        $s .= ' FROM ';
        $s .= implode(', ', array_map(static fn (Table $t) => $t->getDefinition($sqlStyle), $this->from));
        if (count($this->joins) > 0) {
            $s .= ' ' . implode(' ', array_map(static fn (Join $j) => $j->render($sqlStyle), $this->joins));
        }
        if ($this->condition !== null) {
            $s .= ' WHERE ' . $this->condition->render($sqlStyle);
        }
        if (count($this->orderBys) > 0) {
            $s .= ' ORDER BY ' . implode(', ', array_map(static fn (Order $o) => $o->render($sqlStyle), $this->orderBys));
        }
        if (count($this->groupBys) > 0) {
            $s .= ' GROUP BY ' . implode(', ', array_map(static fn (GroupBy $g) => $g->render($sqlStyle), $this->groupBys));
        }
        if ($this->limit !== null) {
            $s .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $s .= ' OFFSET ' . $this->offset;
        }

        if ($omitSemicolon) {
            return $s;
        }

        return $s . ';';
    }

    public function clone(): self
    {
        /** @phpstan-ignore return.type */
        return deep_copy($this);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}