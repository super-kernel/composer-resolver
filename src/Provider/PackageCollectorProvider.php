<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Attribute\Factory;
use SuperKernel\Attribute\Provider;
use SuperKernel\ComposerResolver\Factory\PackageCollectorFactory;
use SuperKernel\ComposerResolver\PackageCollector;
use SuperKernel\Contract\PackageCollectorInterface;
use SuperKernel\Contract\PathResolverInterface;

#[
	Provider(PackageCollectorInterface::class),
	Factory,
]
final class PackageCollectorProvider
{
	private static PackageCollector $packageRegistry;

	public function __invoke(PackageCollectorFactory $collectorFactory, PathResolverInterface $pathResolver): PackageCollectorInterface
	{
		if (!isset(self::$packageRegistry)) {
			self::$packageRegistry = $collectorFactory->create();
			foreach (self::$packageRegistry->getAllPackages() as $package) {
				$package->getClassAutoloader($pathResolver)->register(true);
			}
		}

		return self::$packageRegistry;
	}
}
