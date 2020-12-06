<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core;

use Dhii\PhpCage\Core\Context\Symbol;
use Dhii\PhpCage\Core\Context\SymbolNamespace;

/**
 * A context holds all found, deduced or generated information about symbols in analyzed PHP code.
 *
 * @since [*next-version*]
 */
class Context
{
    /**
     * @since [*next-version*]
     *
     * @var SymbolNamespace[]
     */
    public array $namespaces;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SymbolNamespace[] $namespaces Initial symbol namespaces.
     */
    public function __construct(array $namespaces = [])
    {
        $this->namespaces = $namespaces;
    }

    /**
     * Adds a symbol namespace to the context.
     *
     * @since [*next-version*]
     *
     * @param string          $name  The fully qualified name of the namespace.
     * @param SymbolNamespace $symNs The symbol namespace instance.
     */
    public function addNamespace(string $name, SymbolNamespace $symNs): void
    {
        $this->namespaces[$this->sanitizeFqNs($name)] = $symNs;
    }

    /**
     * Retrieves a symbol namespace.
     *
     * @since [*next-version*]
     *
     * @param string $ns The fully qualified name of the namespace.
     *
     * @return SymbolNamespace|null The symbol namespace instance that corresponds to the given $ns or null if the
     *                              namespace does not exist.
     */
    public function getNamespace(string $ns): ?SymbolNamespace
    {
        $key = $this->sanitizeFqNs($ns);

        return $this->namespaces[$key] ?? null;
    }

    /**
     * Retrieves the contents of the context as a tree.
     *
     * @since [*next-version*]
     *
     * @return SymbolNamespace[]
     */
    public function getTree(): array
    {
        $tree = [];

        foreach ($this->namespaces as $name => $symNs) {
            $qName = QName::fromString($name);
            $count = count($qName->parts);

            $cursor = &$tree;
            for ($i = 0; $i < $count; ++$i) {
                $part = $qName->parts[$i];

                if (!array_key_exists($part, $cursor)) {
                    $cursor[$part] = [];
                }

                if ($i >= ($count - 1)) {
                    $cursor[$part] = $symNs->symbols;
                } else {
                    $cursor = &$cursor[$part];
                }
            }
        }

        return $tree;
    }

    /**
     * Adds a symbol to the correct namespace.
     *
     * @since [*next-version*]
     *
     * @param QName  $fqn    The fully qualified name for the symbol.
     * @param Symbol $symbol The symbol instance to add.
     */
    public function addSymbol(QName $fqn, Symbol $symbol): void
    {
        $name = $fqn->getName();
        $ns = $fqn->getParent()->toString();

        $this->namespaces[$ns] = $this->namespaces[$ns] ?? new SymbolNamespace();
        $this->namespaces[$ns]->addSymbol($name, $symbol);
    }

    /**
     * Searches for a symbol by its fully qualified name.
     *
     * @since [*next-version*]
     *
     * @param QName $fqn The fully qualified name of the symbol.
     *
     * @return Symbol|null The found symbol or null if no symbol was found that corresponds to the given $fqn.
     */
    public function getSymbol(QName $fqn): ?Symbol
    {
        $name = $fqn->getName();
        $ns = $fqn->getParent()->toString();

        if (!array_key_exists($ns, $this->namespaces)) {
            return null;
        }

        return $this->namespaces[$ns]->getSymbol($name);
    }

    /**
     * Sanitizes namespace strings.
     * This is used internally to make sure that the keys for the {@link Context::$namespaces} array property are
     * consistent during insertion, lookup and manipulation operations.
     *
     * @since [*next-version*]
     *
     * @param string $ns The namespace string.
     *
     * @return string The sanitized namespace string.
     */
    protected function sanitizeFqNs(string $ns): string
    {
        return trim($ns, '\\');
    }
}
