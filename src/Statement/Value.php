<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Jojomi\Typer\Str;
use Stringable;
use function is_string;
use function sprintf;

/**
 * Value.
 */
readonly class Value
{

    private function __construct(private string|int|Field|NamedParam|Stringable $value)
    {
        // NOOP
    }

    public static function create(mixed $value): self
    {
        if (!is_string($value) && !is_int($value) && !$value instanceof Stringable && !$value instanceof Field) {
            throw new InvalidArgumentException(sprintf('invalid value %s', Str::fromMixed($value)));
        }

        return new self($value);
    }

    public function render(): string
    {
        $v = $this->value;
        if (is_string($v)) {
            return $this->renderString($v);
        }
        if (is_int($v)) {
            return (string)$v;
        }
        if ($v instanceof Field) {
            return $v->getAccessor();
        }
        if ($v instanceof NamedParam) {
            return $v->getFullName();
        }

        return (string)$v;
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