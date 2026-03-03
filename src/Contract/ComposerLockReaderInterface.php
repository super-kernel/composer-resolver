<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

interface ComposerLockReaderInterface
{
	public function getContentHash(): string;

	public function getPackages(): array;

	public function getPackagesDev(): array;

	public function toArray(): array;
}
