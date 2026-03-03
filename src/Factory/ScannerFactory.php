<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Factory;

use Generator;
use RuntimeException;
use SuperKernel\ComposerResolver\Contract\ScannerInterface;
use SuperKernel\ComposerResolver\Scanner\PcntlScanner;
use SuperKernel\ComposerResolver\Scanner\PharScanner;

final class ScannerFactory
{
	private static ScannerInterface $scanner;

	public static function make(): ScannerInterface
	{
		if (isset(self::$scanner)) {
			return self::$scanner;
		}

		return new self()();
	}

	public function __invoke(): ScannerInterface
	{
		if (!isset(self::$scanner)) {
			self::$scanner = $this->makeScanner();
		}

		return self::$scanner;
	}

	private function makeScanner(): ScannerInterface
	{
		foreach ($this->getScanners() as $scanner) {
			if ($scanner->supports()) {
				return $scanner;
			}
		}

		throw new RuntimeException('No scanner available for metadata scanning.');
	}

	private function getScanners(): Generator
	{
		yield new PcntlScanner();
		yield new PharScanner();
	}
}