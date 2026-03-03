<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Factory;

use AppendIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\Ast\TokenClassParser;
use SuperKernel\ComposerResolver\Contract\PackageInterface;
use SuperKernel\ComposerResolver\Contract\PackageMetadataInterface;
use SuperKernel\ComposerResolver\PackageMetadata;
use function array_filter;
use function array_merge_recursive;
use function array_unique;
use function array_walk_recursive;
use function file_get_contents;
use function is_array;
use function str_replace;
use function trim;

#[
	Provider(PackageMetadataInterface::class),
	Factory,
]
final readonly class PackageMetadataFactory
{

	private TokenClassParser $parser;

	public function __construct()
	{
		$this->parser = new TokenClassParser();
	}

	public function make(PackageInterface $package): PackageMetadataInterface
	{
		$classmap = [];
		/** @var SplFileInfo $file */
		foreach ($this->getIterator($package) as $file) {
			if ('php' === $file->getExtension()) {
				$this->processFile($package, $file, $classmap);
			}
		}

		return new PackageMetadata($package->getName(), $classmap);
	}

	private function getIterator(PackageInterface $package): AppendIterator
	{
		$appendIterator = new AppendIterator();
		$packagePath    = $package->getPathResolver();

		foreach ($this->getScanDirectories($package) as $directory) {
			$path = $packagePath->to($directory)->get();
			if (!is_dir($path)) {
				continue;
			}

			$directory = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);
			$appendIterator->append(new RecursiveIteratorIterator($directory));
		}

		return $appendIterator;
	}

	private function getScanDirectories(PackageInterface $package): array
	{
		$autoloads = $package->getAutoload();

		$autoloads = array_merge_recursive($autoloads, $package->getDevAutoload());

		$dirs = [];
		foreach (['psr-4', 'psr-0'] as $type) {
			if (isset($autoloads[$type]) && is_array($autoloads[$type])) {
				foreach ($autoloads[$type] as $paths) {
					if (is_array($paths)) {
						array_walk_recursive($paths, function ($p) use (&$dirs) {
							$dirs[] = $this->normalizePath((string)$p);
						});
					} else {
						$dirs[] = $this->normalizePath((string)$paths);
					}
				}
			}
		}

		return array_unique(array_filter($dirs));
	}

	private function normalizePath(string $path): string
	{
		return trim($path, "./\\ ");
	}

	private function processFile(PackageInterface $package, SplFileInfo $file, array &$classmap): void
	{
		$realPath = $file->getRealPath();
		$content  = file_get_contents($realPath);

		$classname = $this->parser->getFullyQualifiedClassName($content);

		if ($classname) {
			$classmap[$classname] = str_replace(
				$package->getPathResolver()->get(),
				'',
				$realPath,
			);
		}
	}
}