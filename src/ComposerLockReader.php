<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;

final readonly class ComposerLockReader implements ComposerLockReaderInterface
{
	public function __construct(private array $data)
	{
	}

	public function getContentHash(): string
	{
		return $this->data['content-hash'];
	}

	public function getPackages(): array
	{
		return $this->data['packages'];
	}

	public function getPackagesDev(): array
	{
		return $this->data['packages-dev'];
	}

	public function toArray(): array
	{
		return $this->data;
	}
}
