#!/usr/bin/env php .
<?php
declare(strict_types=1);

use SuperKernel\ComposerResolver\Provider\PackageCollectorProvider;
use SuperKernel\PathResolver\Provider\PathResolverProvider;
use SuperKernel\ProcessHandler\Provider\ProcessHandlerProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$pathResolver = new PathResolverProvider()();
$ProcessHandler = new ProcessHandlerProvider()();

$packageCollector = new PackageCollectorProvider()($pathResolver, $ProcessHandler);

var_dump($packageCollector);