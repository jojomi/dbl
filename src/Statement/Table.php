<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function sprintf;

/**
 * Table.
 */
final readonly class Table
{

    private function __construct(private string $name, private ?string $alias, private bool $raw)
    {
        // NOOP
    }

    public static function create(self|string $name, ?string $alias = null, bool $raw = false): self
    {
        if (!is_string($name)) {
            return $name;
        }

        return new self($name, $alias, $raw);
    }

    public function getDefinition(): string
    {
        $name = $this->getName();
        if ($this->alias === null) {
            return sprintf('%s', $name);
        }

        return sprintf('%s %s', $name, $this->escape($this->alias));
    }

    public function getPrefix(): string
    {
        return sprintf('%s', $this->alias !== null ? $this->escape($this->alias) : $this->getName());
    }

    private function getName(): string
    {
        if ($this->raw) {
            return $this->name;
        }

        return $this->escape($this->name);
    }

    private function escape(string $input): string
    {
        return sprintf('`%s`', $input);
    }

}