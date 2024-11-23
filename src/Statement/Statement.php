<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * Statement.
 */
interface Statement
{
    public function render(bool $omitSemicolon = false): string;

    public function __toString(): string;
}