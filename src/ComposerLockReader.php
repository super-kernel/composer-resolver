<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;

final readonly class ComposerLockReader implements ComposerLockReaderInterface
{
	public function __construct(private array $data)
	{
	}

	public function offsetExists(mixed $offset): bool
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->data[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
	}

	public function offsetUnset(mixed $offset): void
	{
	}
}
