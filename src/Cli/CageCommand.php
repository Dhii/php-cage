<?php

declare(strict_types=1);

namespace Dhii\PhpCage\Cli;

use Dhii\PhpCage\Core\Context;
use Dhii\PhpCage\Core\ContextBuilder;
use Dhii\PhpCage\Core\QName;
use Dhii\PhpCage\Core\Transforms\NamespaceTransform;
use Dhii\PhpCage\Core\Transforms\TransformList;
use Dhii\PhpCage\Core\Unit;
use PhpParser\PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\SplFileInfo;

class CageCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cage')
             ->setDescription('Prefixes the namespace of declarations')
             ->addArgument(
                 'src',
                 InputArgument::REQUIRED,
                 'The src directory to read declarations from'
             )
             ->addOption(
                 'prefix',
                 'p',
                 InputOption::VALUE_REQUIRED,
                 'The namespace prefix',
                 uniqid('PhpCage')
             )
             ->addOption(
                 'output',
                 'o',
                 InputOption::VALUE_REQUIRED,
                 'The output directory',
                 'php-cage-output'
             )
             ->addOption(
                 'include',
                 'i',
                 InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                 'Additional directories to process, but not detect declarations from',
                 []
             )
             ->addOption(
                 'depth',
                 'd',
                 InputOption::VALUE_REQUIRED,
                 'Maximum directory recursion depth',
                 5
             )
             ->addOption(
                 'replace',
                 'r',
                 InputOption::VALUE_NONE,
                 'Replaces the code in-place'
             );
    }

    protected function execute(InputInterface $input, OutputInterface $out): int
    {
        $depth = (int) $input->getOption('depth');
        $srcDir = rtrim($input->getArgument('src'), '\\/');
        $replace = (bool) $input->getOption('replace');
        $outDir = $replace ? $srcDir : rtrim($input->getOption('output'), '\\/');
        $incDirs = $input->getOption('include');
        $prefix = QName::fromString($input->getOption('prefix'));

        $ctx = new Context();
        $builder = new ContextBuilder($ctx);
        $finder = new FileUnitFinder($depth);

        $out->writeln("=> Scanning {$srcDir} ...");

        $files = $finder->findUnits(
            $srcDir,
            function (SplFileInfo $fileInfo, Unit $unit) use ($out, $builder): void {
                $relPath = $fileInfo->getRelativePathname();
                $out->writeln("   {$relPath}", OutputInterface::VERBOSITY_VERY_VERBOSE);

                $builder->buildDecls($unit);
                $builder->buildRefs($unit);
            }
        );

        foreach ($incDirs as $incDir) {
            $out->writeln("=> Scanning {$incDir} ...");

            $incFiles = $finder->findUnits(
                $incDir,
                function (SplFileInfo $fileInfo, Unit $unit) use ($out, $builder): void {
                    $relPath = $fileInfo->getRelativePathname();
                    $out->writeln("   {$relPath}", OutputInterface::VERBOSITY_VERY_VERBOSE);

                    $builder->buildRefs($unit);
                }
            );

            $files = array_merge($files, $incFiles);
        }

        $transforms = [];
        $out->writeln('=> Compiling namespaces ...');

        $nsTree = $ctx->getTree();

        foreach ($nsTree as $ns => $children) {
            if (empty($ns)) {
                continue;
            }

            $ogNs = QName::fromString($ns);
            $newNs = QName::merge($prefix, $ogNs);

            $transforms[] = new NamespaceTransform($ogNs, $newNs);

            $out->writeln(
                "   {$ogNs->toString()} -> {$newNs->toString()}",
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );
        }

        $out->writeln('=> Applying transformation ...');

        $transform = new TransformList($transforms);
        $transform->apply($ctx);

        $out->writeln("=> Printing code to {$outDir} ...");

        $printer = new PrettyPrinter\Standard();
        foreach ($files as $path => $unit) {
            $dir = $outDir . DIRECTORY_SEPARATOR . dirname($path);
            $path = $outDir . DIRECTORY_SEPARATOR . $path;

            $out->writeln("   $path", OutputInterface::VERBOSITY_VERY_VERBOSE);

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($path, $unit->print($printer));
        }

        return 0;
    }
}
