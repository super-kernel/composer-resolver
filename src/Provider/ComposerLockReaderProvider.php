<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use SuperKernel\Attribute\Factory;
use SuperKernel\Attribute\Provider;
use SuperKernel\ComposerResolver\ComposerLockReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;
use SuperKernel\Contract\PathResolverInterface;

#[
	Provider(ComposerLockReaderInterface::class),
	Factory,
]
final class ComposerLockReaderProvider
{
	use JsonReaderTrait;

	private static ComposerLockReaderInterface $composerLockReader;

	/**
	 * @param ContainerInterface $container
	 *
	 * @return ComposerLockReader
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __invoke(ContainerInterface $container): ComposerLockReader
	{
		if (!isset(self::$composerLockReader)) {
			$pathResolver = $container->get(PathResolverInterface::class);

			self::$composerLockReader = new ComposerLockReader(
				self::loadJsonToArray($pathResolver->to('composer.lock')->get()),
			);
		}

		return self::$composerLockReader;
	}
}