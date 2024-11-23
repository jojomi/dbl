<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use function array_map;
use function implode;

/**
 * OrCondition.
 */
final readonly class OrCondition implements Condition
{
    /**
     * @var array<\Jojomi\Dbl\Statement\Condition> $subConditions 
     */
    private array $subConditions;

    private function __construct(Condition ...$subConditions)
    {
        $this->subConditions = $subConditions;
    }

    public static function create(Condition ...$subConditions): self
    {
        return new self(...$subConditions);
    }

    public function withTable(Table $table): static
    {
        return self::create(
            ...array_map(static fn (Condition $subCondition) => $subCondition->withTable($table), $this->subConditions),
        );
    }

    public function render(): string
    {
        if (count($this->subConditions) === 0) {
            return '1=1';
        }

        return implode(
            ' OR ', array_map(
                static function (Condition $c) {
                    $content = $c->render();
                    if ($c->requiresBrackets()) {
                        $content = '(' . $content . ')';
                    }

                    return $content;
                }, $this->subConditions,
            ),
        );
    }

    public function requiresBrackets(): bool
    {
        $numSubConditions = count($this->subConditions);
        if ($numSubConditions === 0) {
            return false;
        }
        if ($numSubConditions === 1) {
            return $this->subConditions[0]->requiresBrackets();
        }

        return true;
    }
}