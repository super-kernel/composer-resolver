<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

interface ComposerJsonReaderInterface
{
	public function getName(): string;

	public function getType(): string;

	public function getReference(): ?string;

	public function getAutoload(): array;

	public function getDevAutoload(): array;

	public function getConfig(?string $name): mixed;

	public function toArray();
}
