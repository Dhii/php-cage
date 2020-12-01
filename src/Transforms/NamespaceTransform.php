<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Transforms;

use Dhii\PhpCage\Context;
use Dhii\PhpCage\QName;
use Dhii\PhpCage\Transform;
use PhpParser\Node;
use RuntimeException;

/**
 * A transformation that renames namespaces.
 *
 * @since [*next-version*]
 */
class NamespaceTransform implements Transform
{
    /** @since [*next-version*] */
    public QName $search;

    /** @since [*next-version*] */
    public QName $replace;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param QName $search  The fully qualified name of the namespace to be transformed.
     * @param QName $replace The fully qualified name of the resulting transformed namespace.
     */
    public function __construct(QName $search, QName $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function apply(Context $context): void
    {
        foreach ($context->namespaces as $nsName => $ns) {
            $oldNsFqn = QName::fromString($nsName);
            $newNsFqn = $oldNsFqn->replace($this->search->parts, $this->replace->parts, 0);

            if ($newNsFqn->isEqualTo($oldNsFqn)) {
                continue;
            }

            foreach ($ns->refs as $ref) {
                if ($ref instanceof Node\Name) {
                    $ref->parts = $newNsFqn->parts;
                } elseif ($ref instanceof Node\Identifier) {
                    $ref->name = $newNsFqn->toString();
                }

                throw new RuntimeException('Invalid node type; cannot rename node');
            }

            foreach ($ns->symbols as $symbol) {
                foreach ($symbol->refs as $ref) {
                    if ($ref instanceof Node\Name) {
                        $oldRefName = new QName($ref->parts);
                        $newRefName = $oldRefName->replace($this->search->parts, $this->replace->parts, 0);
                        $ref->parts = $newRefName->parts;
                    }
                }
            }
        }
    }
}
