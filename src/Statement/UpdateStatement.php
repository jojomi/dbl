<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Stringable;
use function array_map;
use function DeepCopy\deep_copy;
use function implode;
use function sprintf;

/**
 * UpdateStatement.
 */
final class UpdateStatement implements Statement
{
    /** @var array<string, \Jojomi\Dbl\Statement\Value> */
    private array $fieldValues = [];

    private ?Condition $where = null;

    private ?Table $table = null;

    /**
     * @var array<\Jojomi\Dbl\Statement\Order> $orderBys
     */
    private array $orderBys = [];

    private ?int $limit = null;

    private function __construct()
    {
        // NOOP
    }

    public static function create(): self
    {
        return new self();
    }

    public function setTable(Table|string $table): self
    {
        $this->table = Table::create($table);

        return $this;
    }

    public function setValue(Field|string $field, Value|NamedParam|string|int|bool|Stringable $value): self
    {
        $this->fieldValues[Field::create($field)->getAccessor()] = Value::create($value);

        return $this;
    }

    public function where(Condition $condition): self
    {
        $this->where = $condition;

        return $this;
    }

    public function orderBy(Order|Field|string ...$order): self
    {
        foreach ($order as $o) {
            $this->orderBys[] = Order::create($o);
        }

        return $this;
    }

    public function limit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function render(bool $omitSemicolon = false): string
    {
        // validate
        if ($this->table === null) {
            throw new InvalidStatementException(sprintf('missing setTable() call on %s', $this::class));
        }
        if (count($this->fieldValues) < 1) {
            throw new InvalidStatementException(sprintf('missing setField() call on %s', $this::class));
        }

        $updates = [];
        foreach ($this->fieldValues as $field => $value) {
            $updates[] = sprintf('%s = %s', $field, $value->render());
        }

        $s = sprintf(
            'UPDATE %s SET %s',
            $this->table->getDefinition(),
            implode(', ', $updates),
        );

        if ($this->where !== null) {
            $s .= ' WHERE ' . $this->where->render();
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

    public function isNoOp(): bool
    {
        return count($this->fieldValues) === 0;
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