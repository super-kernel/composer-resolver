<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

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

	public function __invoke(PathResolverInterface $pathResolver): ComposerJsonReaderInterface
	{
		if (!isset(self::$composerJsonReader)) {
			self::$composerJsonReader = new ComposerJsonReader(
				self::loadJsonToArray($pathResolver->to('composer.json')->get()),
			);
		}
		return self::$composerJsonReader;
	}
}
