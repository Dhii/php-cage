<?php

namespace Dhii\PhpCage\Test\Func;

use Dhii\PhpCage\QName;
use PHPUnit\Framework\TestCase;

class QNameTest extends TestCase
{
    public function testConstruct()
    {
        $fqn = new QName($parts = ['Test', 'Ns']);

        self::assertEquals($parts, $fqn->parts);
    }

    public function testGetNamespace()
    {
        $fqn = new QName(['Foo', 'Bar', 'Baz']);
        $parent = $fqn->getParent();

        self::assertEquals(['Foo', 'Bar'], $parent->parts);
    }

    public function testToString()
    {
        $fqn = new QName(['Foo', 'Bar', 'Baz']);

        self::assertEquals('Foo\\Bar\\Baz', $fqn->toString());
    }

    public function testStringCast()
    {
        $fqn = new QName(['Foo', 'Bar', 'Baz']);

        self::assertEquals('Foo\\Bar\\Baz', (string) $fqn);
    }

    public function testGetName()
    {
        $fqn = new QName(['Foo', 'Bar', 'Baz']);

        self::assertEquals('Baz', $fqn->getName());
    }

    public function testCreateFromString()
    {
        $fqn = QName::fromString('Foo\\Bar\\Baz');

        self::assertEquals(['Foo', 'Bar', 'Baz'], $fqn->parts);
    }

    public function testFromNsAndName()
    {
        $fqn = QName::fromNsAndName(['Foo', 'Bar'], 'Baz');

        self::assertEquals(['Foo', 'Bar', 'Baz'], $fqn->parts);
    }

    public function testIndexOf1()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz']);
        $index = $subject->indexOf(['Foo']);

        self::assertEquals(0, $index);
    }

    public function testIndexOf2()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz']);
        $index = $subject->indexOf(['Bar']);

        self::assertEquals(1, $index);
    }

    public function testIndexOf3()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz']);
        $index = $subject->indexOf(['Baz']);

        self::assertEquals(2, $index);
    }

    public function testIndexOf4()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz']);
        $index = $subject->indexOf(['Bar', 'Baz']);

        self::assertEquals(1, $index);
    }

    public function testIndexOfFail()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz']);
        $index = $subject->indexOf(['Lorem']);

        self::assertEquals(-1, $index);
    }

    public function testIndexOfFail2()
    {
        $subject = new QName(['Foo', 'Bar', 'Baz', 'Damn']);
        $index = $subject->indexOf(['Bar', 'Damn']);

        self::assertEquals(-1, $index);
    }
}
