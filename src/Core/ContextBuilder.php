<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core;

use Dhii\PhpCage\Core\Visitors\DeclarationFinder;
use Dhii\PhpCage\Core\Visitors\ReferenceFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;

/**
 * Builds a context instance by searching for declarations and references in code units.
 *
 * @since [*next-version*]
 */
class ContextBuilder
{
    /**
     * @since [*next-version*]
     */
    protected NodeTraverser $declPass;

    /**
     * @since [*next-version*]
     */
    protected NodeTraverser $refPass;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Context $ctx The context to build.
     */
    public function __construct(Context $ctx)
    {
        $this->declPass = static::createTraverser([new DeclarationFinder($ctx)]);
        $this->refPass = static::createTraverser([new ReferenceFinder($ctx)]);
    }

    /**
     * Builds the context with symbol declarations and references from a given unit.
     *
     * @since [*next-version*]
     *
     * @param Unit $unit The unit to analyze.
     *
     * @return $this
     */
    public function build(Unit $unit): self
    {
        $this->buildDecls($unit);
        $this->buildRefs($unit);

        return $this;
    }

    /**
     * Builds the context with symbol declarations from a given unit.
     *
     * @since [*next-version*]
     *
     * @param Unit $unit The unit to analyze.
     *
     * @return $this
     */
    public function buildDecls(Unit $unit): self
    {
        $this->declPass->traverse($unit->currAst);

        return $this;
    }

    /**
     * Builds the context with symbol references from a given unit.
     *
     * @since [*next-version*]
     *
     * @param Unit $unit The unit to analyze.
     *
     * @return $this
     */
    public function buildRefs(Unit $unit): self
    {
        $this->refPass->traverse($unit->currAst);

        return $this;
    }

    /**
     * Creates a single node traversing pass.
     *
     * @since [*next-version*]
     *
     * @param NodeVisitor[] $visitors The visitors for this pass.
     *
     * @return NodeTraverser The created node traverser pass.
     */
    protected static function createTraverser(array $visitors = []): NodeTraverser
    {
        $pass = new NodeTraverser();

        // $pass->addVisitor(new ParentConnectingVisitor());
        $pass->addVisitor(new NameResolver(null, [
            'replaceNodes' => false,
            'preserveOriginalNames' => true,
        ]));

        array_walk($visitors, [$pass, 'addVisitor']);

        return $pass;
    }
}
