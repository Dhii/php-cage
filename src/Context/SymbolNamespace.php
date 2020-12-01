<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Context;

use PhpParser\Node;

/**
 * Stores symbols declared in a particular namespace along with references made to that namespace.
 *
 * @since [*next-version*]
 */
class SymbolNamespace
{
    /**
     * @since [*next-version*]
     *
     * @var Node[]
     */
    public array $refs;

    /**
     * @since [*next-version*]
     *
     * @var Symbol[]
     */
    public array $symbols;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Node[]   $refs    The references to the namespace.
     * @param Symbol[] $symbols The symbols in the namespace.
     */
    public function __construct(array $refs = [], array $symbols = [])
    {
        $this->refs = $refs;
        $this->symbols = $symbols;
    }

    /**
     * Adds a reference to the namespace.
     *
     * @since [*next-version*]
     *
     * @param Node $node The node that references the namespace.
     */
    public function addRef(Node $node): void
    {
        $this->refs[] = $node;
    }

    /**
     * Adds a symbol to the namespace.
     *
     * @since [*next-version*]
     *
     * @param string $name   The name of the symbol.
     * @param Symbol $symbol The symbol instance.
     */
    public function addSymbol(string $name, Symbol $symbol): void
    {
        $this->symbols[$name] = $symbol;
    }

    /**
     * Retrieves a symbol by name.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the symbol to retrieve.
     *
     * @return Symbol|null The symbol instance that corresponds to the given $name, or null if not found.
     */
    public function getSymbol(string $name): ?Symbol
    {
        return $this->symbols[$name] ?? null;
    }
}
