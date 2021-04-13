<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core\Transforms;

use Dhii\PhpCage\Core\Context;
use Dhii\PhpCage\Core\Transform;

/**
 * A transform implementation that applies a list of other transformations.
 *
 * @since [*next-version*]
 */
class TransformList implements Transform
{
    /**
     * @since [*next-version*]
     *
     * @var Transform[]
     */
    protected array $transforms;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Transform[] $transforms The transformations to apply.
     */
    public function __construct(array $transforms)
    {
        $this->transforms = $transforms;
    }

    /**
     * @inheritDoc
     *
     * @since [*next-version*]
     */
    public function apply(Context $context): void
    {
        foreach ($this->transforms as $transform) {
            $transform->apply($context);
        }
    }
}
