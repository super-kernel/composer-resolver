<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use Composer\Autoload\ClassLoader;
use RuntimeException;
use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use function spl_autoload_functions;

#[
	Provider(ClassLoader::class),
	Factory,
]
final class ClassLoaderProvider
{
	private static ClassLoader $classLoader;

	public static function make(): ClassLoader
	{
		if (!isset(self::$classLoader)) {
			self::$classLoader = self::getClassLoader();
		}

		return self::$classLoader;
	}

	private static function getClassLoader(): ClassLoader
	{
		foreach (spl_autoload_functions() as $autoload) {
			if (is_array($autoload) && $autoload[0] instanceof ClassLoader) {
				return $autoload[0];
			}
		}

		throw new RuntimeException("No ClassLoader found in registered autoload functions.");
	}

	public function __invoke()
	{
	}
}