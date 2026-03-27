<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use SuperKernel\Attribute\Factory;
use SuperKernel\Attribute\Provider;
use SuperKernel\ComposerResolver\Factory\PackageCollectorFactory;
use SuperKernel\ComposerResolver\PackageCollector;
use SuperKernel\Contract\PackageCollectorInterface;

#[
	Provider(PackageCollectorInterface::class),
	Factory,
]
final class PackageCollectorProvider
{
	private static PackageCollector $packageRegistry;

	/**
	 * @param ContainerInterface $container
	 *
	 * @return PackageCollectorInterface
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __invoke(ContainerInterface $container): PackageCollectorInterface
	{
		if (!isset(self::$packageRegistry)) {
			self::$packageRegistry = $container->get(PackageCollectorFactory::class)->create();
		}

		return self::$packageRegistry;
	}
}
