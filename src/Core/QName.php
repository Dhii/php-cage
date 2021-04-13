<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Core;

/**
 * Represents a qualified name; a.k.a. a potentially namespaced name.
 *
 * @since [*next-version*]
 */
class QName
{
    /**
     * @since [*next-version*]
     *
     * @var string[]
     */
    public array $parts;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string[] $parts The parts of the name.
     */
    public function __construct(array $parts)
    {
        $this->parts = array_filter($parts);
    }

    /**
     * Creates a copy of the qualified name that references this instance's parent.
     *
     * @since [*next-version*]
     */
    public function getParent(): QName
    {
        $parts = count($this->parts) > 1
            ? array_slice($this->parts, 0, -1)
            : [];

        return new QName($parts);
    }

    /**
     * Retrieves the tail part of the qualified name.
     *
     * @since [*next-version*]
     */
    public function getName(): string
    {
        return end($this->parts);
    }

    /**
     * Checks for equivalence between 2 instances.
     *
     * @since [*next-version*]
     *
     * @param QName $other The instance to compare to.
     *
     * @return bool True if the instances are equal, false if not.
     */
    public function isEqualTo(QName $other): bool
    {
        if (count($this->parts) !== count($other->parts)) {
            return false;
        }

        foreach ($this->parts as $idx => $part) {
            if (($other->parts[$idx] ?? null) === $part) {
                return false;
            }
        }

        return true;
    }

    /**
     * Finds the index of a segment list in the qualified name.
     *
     * @since [*next-version*]
     *
     * @param string[] $search The segment list to search for.
     *
     * @return int A 0-based index that corresponds to the index of $search, if it was found. Otherwise, -1 is returned.
     */
    public function indexOf(array $search): int
    {
        if (empty($search)) {
            return -1;
        }

        $neededLength = count($search);
        $foundLength = 0;
        $foundIndex = null;

        foreach ($this->parts as $i => $part) {
            if ($foundLength >= $neededLength) {
                break;
            }

            if ($part === $search[$foundLength]) {
                $foundIndex = $foundIndex ?? $i;
                $foundLength++;
            } elseif ($foundIndex !== null) {
                break;
            }
        }

        return ($foundLength === $neededLength) ? $foundIndex : -1;
    }

    /**
     * Creates a copy of the qualified name with a replaced portion.
     *
     * @since [*next-version*]
     *
     * @param string[] $search  The segment list to search for, which will be replaced by $replace.
     * @param string[] $replace The segment list that will replace $search.
     * @param int      $index   Optional index at which the $search segment list must be found for it to be replaced.
     *
     * @return QName The resulting qualified name instance, which may or may not have a replaced segment list.
     */
    public function replace(array $search, array $replace, int $index = -1): QName
    {
        // Need to copy the state array into another var to have PHP create a copy of it when we array_splice it later
        $output = $this->parts;

        // Get the index of the search segment
        $searchIdx = $this->indexOf($search);

        // If $index is not -1, check if $index matches the $searchIdx. Else, we only splice if the $search was found
        if (($index > -1 && $searchIdx === $index) || ($index < 0 && $searchIdx >= 0)) {
            array_splice($output, $searchIdx, count($search), $replace);
        }

        return new QName($output);
    }

    /**
     * Retrieves the string representation of the qualified name.
     *
     * @since [*next-version*]
     */
    public function toString(): string
    {
        return implode('\\', array_filter($this->parts));
    }

    /**
     * Merges two instances.
     *
     * @since [*next-version*]
     *
     * @param QName $a The first instance.
     * @param QName $b The second instance.
     *
     * @return QName The merged instance, comprised of the first and second instance's parts in the order: A then B.
     */
    public static function merge(QName $a, QName $b)
    {
        return new self(array_merge($a->parts, $b->parts));
    }

    /**
     * Static constructor for creating an instance from a string.
     *
     * @since [*next-version*]
     *
     * @param string $qName The qualified name string.
     *
     * @return QName The created qualified name instance.
     */
    public static function fromString(string $qName): QName
    {
        return new self(explode('\\', $qName));
    }

    /**
     * Static constructor for creating an instance from a namespace array and name string.
     *
     * @since [*next-version*]
     *
     * @param string[] $ns   The namespace name.
     * @param string   $name The name string.
     *
     * @return QName The created qualified name instance.
     */
    public static function fromNsAndName(array $ns, string $name): QName
    {
        $ns[] = $name;

        return new self($ns);
    }

    /**
     * Same as {@link QName::toString()} but for implicit PHP string casting.
     *
     * @since [*next-version*]
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
