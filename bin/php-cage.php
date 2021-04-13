<?php

declare(strict_types=1);

use Dhii\PhpCage\Cli\CageCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->add(new CageCommand());
$app->setDefaultCommand('cage', true);

$app->run();
