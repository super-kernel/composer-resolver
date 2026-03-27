<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use SuperKernel\Attribute\Factory;
use SuperKernel\Attribute\Provider;
use SuperKernel\ComposerResolver\ComposerJsonReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use SuperKernel\Contract\PathResolverInterface;

#[
	Provider(ComposerJsonReaderInterface::class),
	Factory,
]
final class ComposerJsonReaderProvider
{
	use JsonReaderTrait;

	private static ComposerJsonReaderInterface $composerJsonReader;

	/**
	 * @param ContainerInterface $container
	 *
	 * @return ComposerJsonReaderInterface
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __invoke(ContainerInterface $container): ComposerJsonReaderInterface
	{
		if (!isset(self::$composerJsonReader)) {
			$pathResolver = $container->get(PathResolverInterface::class);

			self::$composerJsonReader = new ComposerJsonReader(
				self::loadJsonToArray($pathResolver->to('composer.json')->get()),
			);
		}
		return self::$composerJsonReader;
	}
}
