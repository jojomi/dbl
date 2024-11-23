<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * NamedParam.
 */
final readonly class NamedParam
{
    private string $name;

    private function __construct(string $name)
    {
        $this->name = ltrim($name, ':');
    }

    public static function create(self|string $name): self
    {
        if ($name instanceof self) {
            return $name;
        }

        return new self($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return ':' . $this->name;
    }

}