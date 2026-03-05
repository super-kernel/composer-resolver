<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use SuperKernel\ComposerResolver\Contract\ComposerReaderInterface;

final readonly class ComposerJsonReader implements ComposerReaderInterface
{
	public function __construct(private array $data)
	{
	}

	public function toArray(): array
	{
		return $this->data;
	}
}
