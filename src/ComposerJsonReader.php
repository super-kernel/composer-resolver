<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use ArrayIterator;
use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use Traversable;

final readonly class ComposerJsonReader implements ComposerJsonReaderInterface
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

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->data);
	}
}
