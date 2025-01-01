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

    private function __construct(private string|int|NamedParam|Stringable $value)
    {
        // NOOP
    }

    public static function create(mixed $value): self
    {
        if (!is_string($value) && !is_int($value) && !$value instanceof Stringable) {
            throw new InvalidArgumentException(sprintf('invalid value %s', Str::fromMixed($value)));
        }

        return new self($value);
    }

    public function render(): string
    {
        if (is_string($this->value)) {
            return $this->renderString($this->value);
        }
        if (is_int($this->value)) {
            return (string)$this->value;
        }
        if ($this->value instanceof NamedParam) {
            return $this->value->getFullName();
        }

        return (string)$this->value;
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