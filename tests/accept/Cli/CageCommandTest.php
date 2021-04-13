<?php

namespace Dhii\PhpCage\Test\Accept\Cli;

use Dhii\PhpCage\Cli\CageCommand as Subject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CageCommandTest extends TestCase
{
    public function testCageCommandRunSuccess(): array
    {
        {
            $stubRoot = 'stub';
            $nsRootA = 'Me\Module';
            $nsRootB = 'Me\OtherModule';
            $nsRootC = '';
            $inputDir = "tests/$stubRoot";
            $outputDir = 'tests/output';
            $prefix = 'Cage';

            $command = sprintf('php bin/php-cage.php --output="%2$s" --prefix="%3$s" %1$s', $inputDir, $outputDir, $prefix);
            $exitCode = null;
        }

        {
            $this->cleanArtefacts($inputDir);
            passthru($command, $exitCode);
        }

        {
            $this->assertEquals(0, $exitCode, sprintf('Command "%1$s" failed with code "%2$d"', $command, $exitCode));
        }

        return compact(['stubRoot', 'inputDir', 'outputDir', 'prefix']);
    }

    /**
     * @depends testCageCommandRunSuccess
     */
    public function testRegularPrefix(array $config)
    {
        extract($config);
        $nsRoot = 'Me\Module';
        $className = implode('\\', [$prefix, $nsRoot, 'ThingA']);

        $this->assertTrue(file_exists("$outputDir/$stubRoot/ThingA.php"));
        require_once "$outputDir/$stubRoot/ThingA.php";

        $this->assertTrue(class_exists($className, false), sprintf('Class "%1$s" does not exist', $className));
    }

    /**
     * @depends testCageCommandRunSuccess
     */
    public function testRegularAndDependencyPrefix(array $config)
    {
        extract($config);
        $nsRoot = 'Me\OtherModule';
        $className = implode('\\', [$prefix, $nsRoot, 'ThingB']);
        $classAName = implode('\\', [$nsRoot, 'ThingB']);

        $this->assertTrue(file_exists("$outputDir/$stubRoot/ThingB.php"));
        require_once "$outputDir/$stubRoot/ThingB.php";

        $this->assertTrue(class_exists($className, false), sprintf('Class "%1$s" does not exist', $className));
        $class = new ReflectionClass($className);
        $constructor = $class->getMethod('__construct');
        $args = $constructor->getParameters();
        $this->assertArrayHasKey(0, $args, sprintf('Constructor of "%1$s" does not have parameter 0', $className));
        $arg0 = $args[0];

        $this->assertEquals($classAName, $arg0->getType()->getName());
    }

    /**
     * @depends testCageCommandRunSuccess
     */
    public function testRegularRootNsPrefix(array $config)
    {
        extract($config);
        $nsRoot = '';
        $className = implode('\\', [$nsRoot, 'ThingC']);

        $this->assertTrue(file_exists("$outputDir/$stubRoot/ThingC.php"));
        require_once "$outputDir/$stubRoot/ThingC.php";

        $this->assertTrue(class_exists($className, false), sprintf('Class "%1$s" does not exist', $className));
    }

    protected function cleanArtefacts(string $baseDir)
    {
        passthru(sprintf('rm -rf %1$s/**/*', $baseDir), $exitCode);
    }
}
