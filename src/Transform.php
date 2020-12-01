<?php

namespace Dhii\PhpCage;

/**
 * Represents a transformation that is applied to a context.
 *
 * @since [*next-version*]
 */
interface Transform
{
    /**
     * Applies the transformation.
     *
     * @since [*next-version*]
     *
     * @param Context $context The context to which to apply the transformation.
     */
    public function apply(Context $context): void;
}
