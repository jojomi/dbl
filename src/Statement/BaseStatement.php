<?php

declare(strict_types = 1);

namespace Jojomi\Dbl\Statement;

use Jojomi\Dbl\SqlStyle;use RuntimeException;

/**
 * BaseStatement.
 */
abstract class BaseStatement implements Statement
{
    protected ?SqlStyle $renderStyle = null;

    public function setRenderStyle(SqlStyle $sqlStyle): static
    {
        $this->renderStyle = $sqlStyle;

        return $this;
    }

    public function getRenderStyle(): SqlStyle
    {
        if ($this->renderStyle === null) {
            throw new RuntimeException('No render style set, use setRenderStyle() to set one before doing this call');
        }

        return $this->renderStyle;
    }
}