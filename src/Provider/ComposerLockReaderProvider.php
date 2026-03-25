<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

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

	public static function make(PathResolverInterface $pathResolver): ComposerLockReaderInterface
	{
		if (!isset(self::$composerLockReader)) {
			self::$composerLockReader = new ComposerLockReader(
				self::loadJsonToArray(
					$pathResolver->to('composer.lock')->get(),
				),
			);
		}

		return self::$composerLockReader;
	}

	public function __invoke(PathResolverInterface $pathResolver): ComposerLockReader
	{
		if (!isset(self::$composerLockReader)) {
			self::$composerLockReader = new ComposerLockReader(
				self::loadJsonToArray(
					$pathResolver->to('composer.lock')->get(),
				),
			);
		}

		return self::$composerLockReader;
	}
}