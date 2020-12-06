<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Cli;

use Dhii\PhpCage\Core\Unit;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileUnitFinder
{
    /**
     * @since [*next-version*]
     */
    protected int $depth;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param int $depth The maximum directory recursion depth.
     */
    public function __construct(int $depth)
    {
        $this->depth = $depth;
    }

    /**
     * @since [*next-version*]
     *
     * @param string        $dir      The path to the directory.
     * @param callable|null $callback Optional function to call for each file found. Receives a {@link SplFileInfo} and
     *                                {@link Unit} as arguments.
     *
     * @return Unit[]
     */
    public function findUnits(string $dir, ?callable $callback): array
    {
        $dirName = basename($dir);
        $finder = Finder::create()->depth("< {$this->depth}")->files()->in($dir)->name('*.php');

        $files = [];
        foreach ($finder as $fileInfo) {
            $relPath = $fileInfo->getRelativePathname();
            $absPath = $fileInfo->getRealPath();
            $files[$dirName . DIRECTORY_SEPARATOR . $relPath] = $unit = Unit::createFromFile($absPath);

            $callback($fileInfo, $unit);
        }

        return $files;
    }
}
