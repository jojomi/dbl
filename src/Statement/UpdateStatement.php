<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;

use SplObjectStorage;use Stringable;
use function array_map;
use function DeepCopy\deep_copy;
use function implode;
use function sprintf;

/**
 * UpdateStatement.
 */
final class UpdateStatement extends BaseStatement
{
    /** @var SplObjectStorage<Field, Value> */
    private SplObjectStorage $fieldValues;

    private ?Condition $where = null;

    private ?Table $table = null;

    /**
     * @var array<\Jojomi\Dbl\Statement\Order> $orderBys
     */
    private array $orderBys = [];

    private ?int $limit = null;

    private function __construct()
    {
        $this->fieldValues = new SplObjectStorage();
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
        $this->fieldValues[Field::create($field)] = Value::create($value);

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

    public function render(?SqlStyle $sqlStyle = null, bool $omitSemicolon = false): string
    {
        $sqlStyle ??= $this->getRenderStyle();

        // validate
        if ($this->table === null) {
            throw new InvalidStatementException(sprintf('missing setTable() call on %s', $this::class));
        }
        if (count($this->fieldValues) < 1) {
            throw new InvalidStatementException(sprintf('missing setField() call on %s', $this::class));
        }

        $updates = [];
        foreach ($this->fieldValues as $field) {
            $value = $this->fieldValues[$field];
            $updates[] = sprintf('%s = %s', $field->getAccessor($sqlStyle), $value->render($sqlStyle));
        }

        $s = sprintf(
            'UPDATE %s SET %s',
            $this->table->getDefinition($sqlStyle),
            implode(', ', $updates),
        );

        if ($this->where !== null) {
            $s .= ' WHERE ' . $this->where->render($sqlStyle);
        }

        if ($sqlStyle === SqlStyle::MariaDb) {
            if (count($this->orderBys) > 0) {
                $s .= ' ORDER BY ' . implode(', ', array_map(static fn (Order $o) => $o->render($sqlStyle), $this->orderBys));
            }

            if ($this->limit !== null) {
                $s .= ' LIMIT ' . $this->limit;
            }
        } elseif ($sqlStyle === SqlStyle::Postgres) {
            $needsSubselect = false;
            $subSelect = SelectStatement::create()
                ->from($this->table)
                ->fields('ctid')
            ;
            if (count($this->orderBys) > 0) {
                $needsSubselect = true;
                $subSelect->orderBy(...$this->orderBys);
            }
            if ($this->limit !== null) {
                $needsSubselect = true;
                $subSelect->limit($this->limit);
            }

            if ($needsSubselect) {
                $s .= sprintf(' WHERE "ctid" IN (%s)', $subSelect->render($sqlStyle, omitSemicolon: true));
            }
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