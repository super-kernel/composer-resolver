<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

interface ComposerReaderInterface
{
	public function toArray(): array;
}