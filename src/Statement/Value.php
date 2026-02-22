<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use BackedEnum;
use InvalidArgumentException;
use Jojomi\Dbl\SqlStyle;use Jojomi\Typer\Str;
use Stringable;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Value.
 */
readonly class Value
{

    private function __construct(private string|int|bool|Field|NamedParam|Stringable|BackedEnum|null $value)
    {
        // NOOP
    }

    public static function create(Value|string|int|bool|Field|NamedParam|Stringable|BackedEnum|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return new self($value);
    }

    public function render(SqlStyle $sqlStyle): string
    {
        $v = $this->value;

        if ($v === null) {
            return 'NULL';
        }

        if ($v instanceof BackedEnum) {
            $v = $v->value;
        }

        if (is_int($v)) {
            return (string)$v;
        }
        if (is_bool($v)) {
            return (string)(int)$v;
        }
        if ($v instanceof Field) {
            return $v->getAccessor($sqlStyle);
        }
        if ($v instanceof NamedParam) {
            return $v->getFullName();
        }

        return $this->renderString((string)$v);
    }

    /**
     * Render a string, quoted.
     */
    private function renderString(string $value): string
    {
        $escaped = str_replace(
            ["\\", "\0", "\n", "\r", "\t", "\Z", "'", "\""],
            ["\\\\", "\\0", "\\n", "\\r", "\\t", "\\Z", "''", "\\\""],
            $value,
        );

        return "'{$escaped}'";
    }

}