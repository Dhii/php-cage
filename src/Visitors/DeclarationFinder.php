<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Visitors;

use Dhii\PhpCage\Context;
use Dhii\PhpCage\Context\Symbol;
use PhpParser\Node;

/**
 * Analyzes an AST and extracts declarations.
 *
 * @since [*next-version*]
 */
class DeclarationFinder extends AbstractNsAwareVisitor
{
    /**
     * @since [*next-version*]
     */
    protected Context $ctx;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Context $context The context.
     */
    public function __construct(Context $context)
    {
        $this->ctx = $context;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function beforeTraverse(array $nodes): void
    {
        parent::beforeTraverse($nodes);
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function leaveNode(Node $node)
    {
        if (($node instanceof Node\Stmt\ClassLike || $node instanceof Node\Stmt\Function_) && $node->name !== null) {
            $fqn = $this->resolveQualifiedName($node->name);
            $symbol = new Symbol($node);

            $this->ctx->addSymbol($fqn, $symbol);
        }
    }
}
