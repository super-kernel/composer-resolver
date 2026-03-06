<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\Contract\PackageRegistryInterface;
use SuperKernel\ComposerResolver\Package;
use SuperKernel\ComposerResolver\PackageRegistry;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

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
			$composerJson = ComposerJsonReaderProvider::make()->toArray();
			$composerLock = ComposerLockReaderProvider::make()->toArray();

			$vendorDir = $composerJson['vendor-dir'] ?? 'vendor';
			$pathResolver = PathResolverProvider::make();

			$packages = [];
			foreach (array_merge($composerLock['packages'] ?? [], $composerLock['packages-dev'] ?? []) as $data) {
				$packages[] = new Package($pathResolver->to($vendorDir)->to($data['name']), ...$data);
			}

			$packages [] = new Package($pathResolver, ...$composerJson);
			self::$packageRegistry = new PackageRegistry(...$packages);
		}

		return self::$packageRegistry;
	}

	public function __invoke(): PackageRegistryInterface
	{
		return self::make();
	}
}
