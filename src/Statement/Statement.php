<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;/**
 * Statement.
 */
interface Statement
{
    public function setRenderStyle(SqlStyle $sqlStyle): static;

    public function render(?SqlStyle $sqlStyle = null, bool $omitSemicolon = false): string;

    public function clone(): self;

    public function __toString(): string;
}