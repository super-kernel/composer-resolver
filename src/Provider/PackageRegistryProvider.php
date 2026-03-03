<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\Contract\PackageRegistryInterface;
use SuperKernel\ComposerResolver\PackageRegistry;

#[
	Provider(PackageRegistryInterface::class),
	Factory,
]
final class PackageRegistryProvider
{
	private static PackageRegistry $packageRegistry;

	public static function make(): PackageRegistryInterface
	{
		if (!isset(self::$packageRegistry)) {
			$composerJsonReader = ComposerJsonReaderProvider::make();
			$composerLockReader = ComposerLockReaderProvider::make();

			self::$packageRegistry = new PackageRegistry($composerJsonReader, $composerLockReader);
		}

		return self::$packageRegistry;
	}

	public function __invoke(): PackageRegistryInterface
	{
		return self::make();
	}
}
