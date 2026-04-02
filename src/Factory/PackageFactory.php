<?php

declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Factory;

use FilesystemIterator;
use Generator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SuperKernel\ComposerResolver\Package;
use SuperKernel\Contract\PackageCollectorInterface;
use SuperKernel\Contract\PackageInterface;
use SuperKernel\Contract\PathResolverInterface;
use SuperKernel\Tokenizer\Reader\FullTokenReader;
use SuperKernel\Tokenizer\TokenParser;
use function array_all;
use function array_merge;
use function is_dir;
use function is_file;
use function realpath;
use function str_replace;
use function str_starts_with;
use function ltrim;
use function rtrim;
use function substr;
use function strlen;

final class PackageFactory
{
	private const array SCAN_KEYS = ['psr-4', 'psr-0', 'classmap'];

	private readonly array $autoload;

	private readonly array $autoloadDev;

	private readonly string $basePath;

	private readonly ?string $type;

	public function __construct(
		private readonly PathResolverInterface $pathResolver,
		private readonly array                 $rawData,
		private readonly ?string               $vendorDir,
	)
	{
		$this->autoload = $rawData['autoload'] ?? [];
		$this->autoloadDev = $rawData['autoload-dev'] ?? [];
		$this->basePath = $this->normalizePath($this->pathResolver->get());
		$this->type = $vendorDir === null ? PackageCollectorInterface::ROOT : null;
	}

	public function create(): PackageInterface
	{
		return new Package(
			rawData : $this->rawData,
			files   : $this->getFiles(),
			classMap: $this->getClassMap(),
			type    : $this->type,
		);
	}

	private function getClassMap(): array
	{
		$classMap = [];
		foreach ($this->getScanningIterator() as $file) {
			if (!$file->isFile() || $file->getExtension() !== 'php') {
				continue;
			}

			$path = $file->getRealPath();
			if ($path === false) {
				continue;
			}

			$reader = new FullTokenReader($path);
			$tokenParser = TokenParser::parse($reader);

			if (!$tokenParser->isValid()) {
				continue;
			}

			$className = $tokenParser->getClassName();
			if ($className === null) {
				continue;
			}

			$relativePath = $this->getRelativePath($path);
			if ($relativePath !== null) {
				$classMap[$className] = $relativePath;
			}
		}

		return $classMap;
	}

	private function getScanningIterator(): Generator
	{
		$resolver = $this->pathResolver;
		if ($this->vendorDir !== null && isset($this->rawData['name'])) {
			$resolver = $resolver->to($this->vendorDir)->to($this->rawData['name']);
		}

		$autoload = $this->autoload;
		$excludes = (array)($this->autoload['exclude-from-classmap'] ?? []);

		if (null === $this->vendorDir) {
			$autoload = array_merge_recursive($autoload, $this->autoloadDev);
			$excludes = array_merge_recursive($excludes, (array)($this->autoloadDev['exclude-from-classmap'] ?? []));
		}

		$absoluteExcludes = [];
		foreach ($excludes as $excludePath) {
			$resolvedExclude = realpath($resolver->to($excludePath)->get());
			if ($resolvedExclude) {
				$absoluteExcludes[] = $this->normalizePath($resolvedExclude);
			}
		}

		foreach (self::SCAN_KEYS as $key) {
			$entries = (array)($autoload[$key] ?? []);

			foreach ($entries as $paths) {
				foreach ((array)$paths as $path) {
					$resolvedPath = $resolver->to($path)->get();
					$fullPath = realpath($resolvedPath);

					if (!$fullPath) {
						continue;
					}

					if (is_dir($fullPath)) {
						$directoryIterator = new RecursiveDirectoryIterator(
							$fullPath,
							FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS,
						);

						$filterIterator = new RecursiveCallbackFilterIterator(
							$directoryIterator,
							function (SplFileInfo $current) use ($absoluteExcludes) {
								$currentPath = $this->normalizePath($current->getRealPath() ?: '');

								return array_all(
									array   : $absoluteExcludes,
									callback: fn($exclude) => $currentPath !== $exclude && !str_starts_with($currentPath, $exclude . '/'),
								);
							},
						);

						$iterator = new RecursiveIteratorIterator($filterIterator);
						foreach ($iterator as $file) {
							yield $file;
						}
					} elseif (is_file($fullPath)) {
						yield new SplFileInfo($fullPath);
					}
				}
			}
		}
	}

	private function getFiles(): array
	{
		$resolver = $this->pathResolver;
		if ($this->vendorDir !== null && isset($this->rawData['name'])) {
			$resolver = $resolver->to($this->vendorDir)->to($this->rawData['name']);
		}

		$autoloadFiles = (array)($this->autoload['files'] ?? []);
		if (null === $this->vendorDir) {
			$autoloadFiles = array_merge($autoloadFiles, (array)($this->autoloadDev['files'] ?? []));
		}

		$files = [];

		foreach ($autoloadFiles as $autoloadFile) {
			$fullPath = $resolver->to($autoloadFile)->get();
			$relative = $this->getRelativePath($fullPath);
			if ($relative !== null) {
				$files[] = $relative;
			}
		}

		return $files;
	}

	private function getRelativePath(string $fullPath): ?string
	{
		$normalizedFull = $this->normalizePath($fullPath);

		if (str_starts_with($normalizedFull, $this->basePath)) {
			return ltrim(substr($normalizedFull, strlen($this->basePath)), '/');
		}

		return null;
	}

	private function normalizePath(string $path): string
	{
		return rtrim(str_replace('\\', '/', $path), '/');
	}
}