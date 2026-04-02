<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ClassLoader\ClassLoader;
use SuperKernel\Contract\ClassLoaderInterface;
use SuperKernel\Contract\PackageInterface;

final readonly class Package implements PackageInterface
{
	private ?string $name;

	private string $type;

	private ?string $reference;

	private ClassLoaderInterface $classLoader;

	public function __construct(
		private array $rawData,
		private array $files,
		array         $classMap,
		?string       $type = null,
	)
	{
		$this->name = $this->rawData['name'] ?? null;
		$this->type = $type ?? $this->rawData['type'] ?? null;
		$this->reference = $this->rawData['dist']['reference'] ?? null;
		$this->classLoader = new ClassLoader($classMap);
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

	public function getClassAutoloader(): ClassLoaderInterface
	{
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