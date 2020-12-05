<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core;

use Dhii\PhpCage\Core\Core\Unit;
use Dhii\PhpCage\Core\Visitors\DeclarationFinder;
use Dhii\PhpCage\Core\Visitors\ReferenceFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter;
use PhpParser\PrettyPrinterAbstract;

class Cage
{
    /**
     * @since [*next-version*]
     */
    protected Context $ctx;

    /**
     * @since [*next-version*]
     */
    protected Transform $transform;

    /**
     * @since [*next-version*]
     *
     * @var NodeTraverser[]
     */
    protected array $passes;

    /**
     * @since [*next-version*]
     */
    protected PrettyPrinterAbstract $printer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Context               $ctx       The context.
     * @param Transform             $transform The transformation to apply to the code.
     * @param PrettyPrinterAbstract $printer   The code printer.
     */
    public function __construct(Context $ctx, Transform $transform, PrettyPrinterAbstract $printer)
    {
        $this->ctx = $ctx;
        $this->transform = $transform;
        $this->printer = $printer;
        $this->passes = $this->createPasses();
    }

    /**
     * Analyzes a unit.
     *
     * @since [*next-version*]
     *
     * @param Unit $unit The unit to analyze.
     */
    public function analyze(Unit $unit): void
    {
        foreach ($this->passes as $pass) {
            $pass->traverse($unit->currAst);
        }
    }

    /**
     * Runs the refactoring.
     *
     * @since [*next-version*]
     */
    public function run(): void
    {
        $this->transform->apply($this->ctx);
    }

    /**
     * Creates the passes.
     *
     * @since [*next-version*]
     *
     * @return NodeTraverser[] The created node traverser passes.
     */
    public function createPasses(): array
    {
        $declFinder = new DeclarationFinder($this->ctx);
        $refFinder = new ReferenceFinder($this->ctx);

        return [
            static::createPass([$declFinder]),
            static::createPass([$refFinder]),
        ];
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
    public static function createPass(array $visitors = []): NodeTraverser
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

    /**
     * Static constructor that creates an instance using just a transform.
     *
     * @since [*next-version*]
     *
     * @param Transform $transform The transform to apply.
     *
     * @return Cage The created instance.
     */
    public static function create(Transform $transform): Cage
    {
        return new self(new Context(), $transform, new PrettyPrinter\Standard());
    }
}
