<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use BackedEnum;
use InvalidArgumentException;
use Jojomi\Typer\Str;
use Stringable;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Value.
 */
readonly class Value
{

    private function __construct(private string|int|bool|Field|NamedParam|Stringable|BackedEnum $value)
    {
        // NOOP
    }

    public static function create(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }
        if (!is_string($value) && !is_int($value) && !is_bool($value) &&
        !$value instanceof Stringable && !$value instanceof Field && !$value instanceof BackedEnum) {
            throw new InvalidArgumentException(sprintf('invalid value %s', Str::fromMixed($value)));
        }

        return new self($value);
    }

    public function render(): string
    {
        $v = $this->value;

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
            return $v->getAccessor();
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