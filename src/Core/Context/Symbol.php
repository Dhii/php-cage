<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core\Context;

use PhpParser\Node;

/**
 * Represents a symbol.
 *
 * @since [*next-version*]
 */
class Symbol
{
    /**
     * @since [*next-version*]
     */
    public ?Node $decl;

    /**
     * @since [*next-version*]
     *
     * @var Node[]
     */
    public array $refs;

    /**
     * Constructor.
     *
     * @param Node   $decl The declaration node for this symbol.
     * @param Node[] $refs The nodes that reference this symbol.
     */
    public function __construct(Node $decl, array $refs = [])
    {
        $this->decl = $decl;
        $this->refs = $refs;
    }
}
