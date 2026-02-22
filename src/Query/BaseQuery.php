<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Query;

use DateTimeInterface;
use Jojomi\Dbl\SqlStyle;
use Jojomi\Dbl\Statement\NamedParam;
use Jojomi\Dbl\Statement\Statement;
use PDO;
use PDOStatement;
use RuntimeException;
use Stringable;
use function is_int;
use function print_r;
use function sprintf;

/**
 * BaseQuery.
 *
 * @template T
 *
 * @implements \Jojomi\Dbl\Query\Query<T>
 */
abstract class BaseQuery implements Query
{
    /**
     * @var array<array{name: string, value: int|string|null}>
     */
    protected array $params = [];

    abstract protected function getQuery(): Statement|string;

    /**
     * Returns a prepared statement ready to be executed. Optionally with parameter values already bound.
     */
    public function getPreparedStatement(PDO $conn, SqlStyle $sqlStyle, bool $autoBind = true): PDOStatement
    {
        $query = $this->getQuery();
        if ($query instanceof Statement) {
            $query = $query->render($sqlStyle);
        }

        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new RuntimeException();
        }

        if (!$autoBind) {
            return $stmt;
        }

        $this->setParams();
        $this->bindParams($stmt);

        return $stmt;
    }

    protected function addParam(NamedParam|string $name, string|int|float|Stringable|null $value): static
    {
        $name = NamedParam::create($name)->getName();

        $value = match (true) {
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof Stringable || is_float($value) => (string)$value,
            default => $value,
        };

        $this->params[] = [
            'name' => $name,
            'value' => $value,
        ];

        return $this;
    }

    protected function bindParams(PDOStatement $stmt): static
    {
        if (count($this->params) === 0) {
            return $this;
        }

        foreach ($this->params as $param) {
            $value = $param['value'];
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($param['name'], $value, $type);
        }

        return $this;
    }

    protected function tableString(string $tableName): string
    {
        return sprintf('`%s`', $tableName);
    }

    protected function columnString(string $columnName): string
    {
        return sprintf('`%s`', $columnName);
    }

    protected function setParams(): static
    {
        // NOOP
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s, parameters: %s',
            static::class,
            $this->getQuery(),
            print_r($this->params, true),
        );
    }

}