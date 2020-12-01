<?php

declare(strict_types=1);

namespace Dhii\PhpCage;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;

/**
 * A struct that represents a single unit of code.
 *
 * @since [*next-version*]
 */
class Unit
{
    /**
     * @since [*next-version*]
     *
     * @var Node[]
     */
    public array $ogAst;

    /**
     * @since [*next-version*]
     *
     * @var Node[]
     */
    public array $currAst;

    /**
     * @since [*next-version*]
     *
     * @var int[]
     */
    public array $tokens;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Node[] $ogAst   The original AST for this unit.
     * @param Node[] $currAst The current AST for this unit.
     * @param int[]  $tokens  The tokens for this unit.
     */
    public function __construct(array $ogAst, array $currAst, array $tokens)
    {
        $this->ogAst = $ogAst;
        $this->currAst = $currAst;
        $this->tokens = $tokens;
    }

    /**
     * Prints the unit's current AST as PHP code, preserving as much formatting as possible.
     *
     * @since [*next-version*]
     *
     * @param PrettyPrinterAbstract $printer The printer instance to use for printing.
     *
     * @return string The printed code.
     */
    public function print(PrettyPrinterAbstract $printer): string
    {
        return $printer->printFormatPreserving($this->currAst, $this->ogAst, $this->tokens);
    }

    /**
     * Creates an instance by parsing PHP code.
     *
     * @since [*next-version*]
     *
     * @param string $code The PHP code as a string.
     *
     * @return Unit The unit that represents the given code.
     */
    public static function createFromCode(string $code): Unit
    {
        // Create Parser
        $lexer = new Lexer\Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);
        $parser = new Parser\Php7($lexer);

        // Parse file
        $ogAst = $parser->parse($code);
        $tokens = $lexer->getTokens();

        // Clone the AST
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new CloningVisitor());
        $currAst = $traverser->traverse($ogAst);

        return new Unit($ogAst, $currAst, $tokens);
    }

    /**
     * Creates an instance by parsing a PHP file.
     *
     * @since [*next-version*]
     *
     * @param string $filepath The path to the PHP file to parse.
     *
     * @return Unit The unit that represents the given code.
     */
    public static function createFromFile(string $filepath): Unit
    {
        return static::createFromCode(file_get_contents($filepath));
    }
}
