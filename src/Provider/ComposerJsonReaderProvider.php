<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\ComposerJsonReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

#[
	Provider(ComposerJsonReaderInterface::class),
	Factory,
]
final class ComposerJsonReaderProvider
{
	use JsonReaderTrait;

	private static ComposerJsonReaderInterface $composerJsonReader;

	public static function make(): ComposerJsonReaderInterface
	{
		if (!isset(self::$composerJsonReader)) {
			$pathResolver = PathResolverProvider::make();

			self::$composerJsonReader = new ComposerJsonReader(
				self::loadJsonToArray($pathResolver->to('composer.json')->get()),
			);
		}

		return self::$composerJsonReader;
	}

	public function __invoke(): ComposerJsonReaderInterface
	{
		return self::make();
	}
}
