<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ClassLoader\ClassLoader;
use SuperKernel\Contract\ClassLoaderInterface;
use SuperKernel\Contract\PackageInterface;
use SuperKernel\Contract\PathResolverInterface;
use function array_map;

final class Package implements PackageInterface
{
	private readonly ?string $name;

	private readonly string $type;

	private readonly ?string $reference;

	private ClassLoaderInterface $classLoader;

	public function __construct(
		private readonly array $rawData,
		private readonly array $files,
		private readonly array $classMap,
		?string                $type = null,
	)
	{
		$this->name = $this->rawData['name'] ?? null;
		$this->type = $type ?? $this->rawData['type'] ?? null;
		$this->reference = $this->rawData['dist']['reference'] ?? null;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getReference(): ?string
	{
		return $this->reference;
	}

	public function getClassAutoloader(PathResolverInterface $pathResolver): ClassLoaderInterface
	{
		if (!isset($this->classLoader)) {
			$classMap = array_map(function ($path) use ($pathResolver) {
				return $pathResolver->to($path)->get();
			}, $this->classMap);
			$this->classLoader = new ClassLoader($classMap);
		}
		return $this->classLoader;
	}

	public function getFiles(): array
	{
		return $this->files;
	}

	public function getRawData(): array
	{
		return $this->rawData;
	}
}