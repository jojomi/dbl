<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

/**
 * Condition.
 */
interface Condition
{
    public function render(): string;

    /**
     * Noop iff $table is already set.
     */
    public function withTable(Table $table): static;

    public function requiresBrackets(): bool;
}