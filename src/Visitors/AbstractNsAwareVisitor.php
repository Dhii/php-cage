<?php

namespace Dhii\PhpCage\Visitors;

use Dhii\PhpCage\QName;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * Abstract node visitor that maintains a namespace stack.
 *
 * @since [*next-version*]
 */
abstract class AbstractNsAwareVisitor extends NodeVisitorAbstract
{
    /**
     * Namespace stack.
     *
     * @var string[][]
     */
    protected array $nsStack;

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function beforeTraverse(array $nodes)
    {
        $this->nsStack = [];
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            array_unshift($this->nsStack, $node->name->parts);
        }
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            array_shift($this->nsStack);
        }
    }

    /**
     * Creates a qualified name for a node.
     *
     * @since [*next-version*]
     *
     * @param Node $node The node.
     *
     * @return QName|null The qualified name or null if the node is not a {@link Node\Name} or {@link Node\Identifier}
     *                    instance.
     */
    public function resolveQualifiedName(Node $node): ?QName
    {
        if ($node instanceof Node\Name) {
            $parts = $node->hasAttribute('resolvedName')
                ? $node->getAttribute('resolvedName')->parts
                : $node->parts;

            return new QName($parts);
        } elseif ($node instanceof Node\Identifier) {
            $ns = $this->nsStack[0] ?? [];
            $name = $node->toString();

            return QName::fromNsAndName($ns, $name);
        }

        return null;
    }
}
