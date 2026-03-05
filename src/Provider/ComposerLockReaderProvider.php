<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\ComposerLockReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

#[
	Provider(ComposerLockReader::class),
	Factory,
]
final class ComposerLockReaderProvider
{
	use JsonReaderTrait;

	private static ComposerLockReader $composerLockReader;

	public static function make(): ComposerLockReader
	{
		if (!isset(self::$composerLockReader)) {
			self::$composerLockReader = new ComposerLockReader(
				self::loadJsonToArray(
					PathResolverProvider::make()->to('composer.lock')->get(),
				),
			);
		}

		return self::$composerLockReader;
	}

	public function __invoke(): ComposerLockReader
	{
		return self::make();
	}
}