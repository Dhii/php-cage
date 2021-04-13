<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core\Visitors;

use Dhii\PhpCage\Core\Context;
use PhpParser\Node;

/**
 * A node visitor that detects and records references to symbols in a given {@link Context} instance.
 *
 * @since [*next-version*]
 */
class ReferenceFinder extends AbstractNsAwareVisitor
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
     * @param Context $ctx The context.
     */
    public function __construct(Context $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function beforeTraverse(array $nodes)
    {
        parent::beforeTraverse($nodes);
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function enterNode(Node $node)
    {
        parent::enterNode($node);
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Name || $node instanceof Node\Identifier) {
            $fqn = $this->resolveQualifiedName($node);

            $symbol = $this->ctx->getSymbol($fqn);

            if ($symbol !== null && $symbol->decl !== null) {
                $symbol->refs[] = $node;
            }
        } elseif ($node instanceof Node\Stmt\Namespace_) {
            $ns = $this->ctx->getNamespace($node->name->toString());

            if ($ns !== null) {
                $ns->addRef($node->name);
            }
        }
    }
}
