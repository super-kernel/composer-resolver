<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

interface ScannerInterface
{
	public function supports(): bool;

	public function execute(callable $task): void;
}