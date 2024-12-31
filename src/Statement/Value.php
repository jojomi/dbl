<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use InvalidArgumentException;
use Jojomi\Typer\Str;
use function is_string;
use function sprintf;

/**
 * Value.
 */
readonly class Value
{

    private function __construct(private string|int|NamedParam $value)
    {
        // NOOP
    }

    public static function create(mixed $value): self
    {
        if (!is_string($value) && !is_int($value) && !$value instanceof NamedParam) {
            throw new InvalidArgumentException(sprintf('invalid value %s', Str::fromMixed($value)));
        }

        return new self($value);
    }

    public function render(): string
    {
        if (is_string($this->value)) {
            return "'" . $this->value . "'";
        }
        if (is_int($this->value)) {
            return (string)$this->value;
        }

        return $this->value->getFullName();
    }

}