#!/usr/bin/env php .
<?php
declare(strict_types=1);

use SuperKernel\ComposerResolver\Provider\PackageMetadataRegistryProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$packageMetadataRegistry = new PackageMetadataRegistryProvider()();

var_dump(
	$packageMetadataRegistry->getPackages(),
);