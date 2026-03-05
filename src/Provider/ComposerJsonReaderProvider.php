<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\ComposerJsonReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

#[
	Provider(ComposerJsonReader::class),
	Factory,
]
final class ComposerJsonReaderProvider
{
	use JsonReaderTrait;

	private static ComposerJsonReader $composerJsonReader;

	public static function make(): ComposerJsonReader
	{
		if (!isset(self::$composerJsonReader)) {
			$pathResolver = PathResolverProvider::make();

			self::$composerJsonReader = new ComposerJsonReader(
				self::loadJsonToArray($pathResolver->to('composer.json')->get()),
			);
		}

		return self::$composerJsonReader;
	}

	public function __invoke(): ComposerJsonReader
	{
		return self::make();
	}
}
