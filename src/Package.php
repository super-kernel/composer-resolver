<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\Contract\PackageInterface;

final readonly class Package implements PackageInterface
{
	private ?string $name;

	private string $type;

	private ?string $reference;

	public function __construct(
		private array $rawData,
		private array $classMap,
		private array $files,
		?string       $type = null,
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

	public function getClassMap(): array
	{
		return $this->classMap;
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