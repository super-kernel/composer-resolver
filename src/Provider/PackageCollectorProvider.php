<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Attribute\Factory;
use SuperKernel\Attribute\Provider;
use SuperKernel\ComposerResolver\Factory\PackageCollectorFactory;
use SuperKernel\ComposerResolver\PackageCollector;
use SuperKernel\Contract\PackageCollectorInterface;
use SuperKernel\Contract\PathResolverInterface;
use SuperKernel\ProcessHandler\Contract\ProcessHandlerInterface;

#[
	Provider(PackageCollectorInterface::class),
	Factory,
]
final class PackageCollectorProvider
{
	private static PackageCollector $packageRegistry;

	public function __invoke(
		PathResolverInterface   $pathResolver,
		ProcessHandlerInterface $processHandler,
	): PackageCollectorInterface
	{
		if (!isset(self::$packageRegistry)) {
			self::$packageRegistry = new PackageCollectorFactory($pathResolver, $processHandler)->create();
		}

		return self::$packageRegistry;
	}
}
