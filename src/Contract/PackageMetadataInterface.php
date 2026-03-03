<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

interface PackageMetadataInterface
{
	public function getName(): string;

	public function getReference(): ?string;

	public function getClassmap(): array;
}