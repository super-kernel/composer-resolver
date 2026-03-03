<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use function is_null;

final readonly class ComposerJsonReader implements ComposerJsonReaderInterface
{
	public function __construct(private array $data)
	{
	}

	public function getName(): string
	{
		return $this->data['name'];
	}

	public function getType(): string
	{
		return $this->data['type'] ?? 'project';
	}

	public function getReference(): ?string
	{
		return $this->data['reference'];
	}

	public function getAutoload(): array
	{
		return $this->data['autoload'] ?? [];
	}

	public function getDevAutoload(): array
	{
		return $this->data['autoload-dev'] ?? [];
	}

	public function getConfig(?string $name): mixed
	{
		if (is_null($name)) {
			return $this->data['config'];
		}

		return $this->data['config'][$name] ?? null;
	}

	public function toArray(): array
	{
		return $this->data;
	}
}
