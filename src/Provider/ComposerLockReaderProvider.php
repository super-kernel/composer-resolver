<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\ComposerLockReader;
use SuperKernel\ComposerResolver\Concerns\JsonReaderTrait;
use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

#[
	Provider(ComposerLockReaderInterface::class),
	Factory,
]
final class ComposerLockReaderProvider
{
	use JsonReaderTrait;

	private static ComposerLockReaderInterface $composerLockReader;

	public static function make(): ComposerLockReaderInterface
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

	public function __invoke(): ComposerLockReaderInterface
	{
		return self::make();
	}
}