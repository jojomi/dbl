<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use function array_map;
use function DeepCopy\deep_copy;
use function implode;
use function is_string;
use function sprintf;

/**
 * DeleteStatement.
 */
final class DeleteStatement implements Statement
{
    /**
     * @var array<\Jojomi\Dbl\Statement\Table> $from
     */
    private array $from = [];

    /**
     * @var array<\Jojomi\Dbl\Statement\Order> $orderBys
     */
    private array $orderBys = [];

    /**
     * @var array<\Jojomi\Dbl\Statement\Join> $joins
     */
    private array $joins = [];

    private ?int $limit = null;

    private ?Condition $condition = null;

    private ?Table $currentTable = null;

    private bool $all = false;

    private function __construct()
    {
        // NOOP
    }

    public static function create(): self
    {
        return new self();
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

    public function where(Condition $condition): self
    {
        if ($this->currentTable !== null) {
            $condition = $condition->withTable($this->currentTable);
        }

        $this->condition = $this->condition !== null ? AndCondition::create($this->condition, $condition) : $condition;

        return $this;
    }

    public function all(): self
    {
        $this->all = true;

        return $this;
    }

    public function orderBy(Order ...$order): self
    {
        foreach ($order as $o) {
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

    public function render(bool $omitSemicolon = false): string
    {
        if (count($this->from) === 0) {
            throw new InvalidStatementException('missing FROM on statement');
        }

        // safeguard
        if (!$this->all && ($this->condition === null && $this->limit === null)) {
            throw new InvalidArgumentException(
                sprintf(
                    "SafeGuard: This query might delete all rows in %s. Either call all(), specify conditions using where(), or call limit()",
                    implode(", ", array_map(static fn (Table $table) => $table->getDefinition(), $this->from)),
                ),
            );
        }

        $s = 'DELETE FROM ';
        $s .= implode(', ', array_map(static fn (Table $t) => $t->getDefinition(), $this->from));
        if (count($this->joins) > 0) {
            $s .= ' ' . implode(' ', array_map(static fn (Join $j) => $j->render(), $this->joins));
        }
        if ($this->condition !== null) {
            $s .= ' WHERE ' . $this->condition->render();
        }
        if (count($this->orderBys) > 0) {
            $s .= ' ORDER BY ' . implode(', ', array_map(static fn (Order $o) => $o->render(), $this->orderBys));
        }
        if ($this->limit !== null) {
            $s .= ' LIMIT ' . $this->limit;
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