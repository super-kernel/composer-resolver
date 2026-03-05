<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Provider;

use Phar;
use RuntimeException;
use SuperKernel\Annotation\Factory;
use SuperKernel\Annotation\Provider;
use SuperKernel\ComposerResolver\Contract\PackageInterface;
use SuperKernel\ComposerResolver\Contract\PackageMetadataInterface;
use SuperKernel\ComposerResolver\Contract\PackageMetadataRegistryInterface;
use SuperKernel\ComposerResolver\Factory\PackageMetadataFactory;
use SuperKernel\ComposerResolver\Factory\ScannerFactory;
use SuperKernel\ComposerResolver\PackageMetadata;
use SuperKernel\ComposerResolver\PackageMetadataRegistry;
use SuperKernel\PathResolver\Contract\PathResolverInterface;
use SuperKernel\PathResolver\Provider\PathResolverProvider;
use function file_put_contents;
use function is_dir;
use function is_file;
use function is_null;
use function mkdir;
use function serialize;
use function str_replace;
use function strlen;
use function unserialize;

#[
	Provider(PackageMetadataRegistryInterface::class),
	Factory,
]
final class PackageMetadataRegistryProvider
{
	private static PackageMetadataRegistryInterface $packageMetadataRegistry;

	private static PathResolverInterface $pathResolver;

	private static function getPathResolver(): PathResolverInterface
	{
		if (!isset(self::$pathResolver)) {
			self::$pathResolver = PathResolverProvider::make()
				->to('vendor')
				->to('.super-kernel')
				->to('package-metadata');

			$dir = self::$pathResolver->get();
			if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
				throw new RuntimeException("Could not create cache dir: $dir");
			}
		}
		return self::$pathResolver;
	}

	public static function make(): PackageMetadataRegistryInterface
	{
		if (!isset(self::$packageMetadataRegistry)) {
			$packageRegistry = PackageRegistryProvider::make();

			$packagesMetadata = [];
			foreach ($packageRegistry->getPackages() as $package) {
				$packagesMetadata[] = self::resolveMetadata($package);
			}

			self::$packageMetadataRegistry = new PackageMetadataRegistry(...$packagesMetadata);

			$classLoader = ClassLoaderProvider::make();
			foreach (self::$packageMetadataRegistry->getPackages() as $package) {
				$packageName = $package->getName();
				$pathResolver = $packageRegistry->getPackage($packageName)->getPathResolver();
				foreach ($package->getClassmap() as $class => $path) {
					$classLoader->addClassMap([$class => $pathResolver->to($path)->get()]);
				}
			}
		}

		return self::$packageMetadataRegistry;
	}

	private static function resolveMetadata(PackageInterface $package): PackageMetadataInterface
	{
		$fileName = str_replace(['/', '\\'], '_', $package->getName());
		$filePath = self::getPathResolver()->to($fileName)->get();

		$isPhar = strlen(Phar::running(false)) > 0;
		if ($isPhar) {
			return self::loadCache($filePath);
		}

		if (is_null($package->getReference())) {
			return self::scan($package, $filePath);
		}

		$cachePackage = self::loadCache($filePath);
		if ($cachePackage?->getReference() !== $package->getReference()) {
			return self::scan($package, $filePath);
		}

		return $cachePackage;
	}

	private static function scan(PackageInterface $package, string $filePath): ?PackageMetadataInterface
	{
		ScannerFactory::make()->execute(function () use ($package, $filePath) {
			$metadata = PackageMetadataFactory::make($package);
			file_put_contents($filePath, serialize($metadata), LOCK_EX);
		});

		return self::loadCache($filePath) ?? throw new RuntimeException("Scan failed for {$package->getName()}");
	}

	private static function loadCache(string $path): ?PackageMetadataInterface
	{
		if (!is_file($path)) return null;
		$content = file_get_contents($path);
		if (!$content) return null;

		$data = unserialize($content, ['allowed_classes' => [PackageMetadata::class]]);
		return $data instanceof PackageMetadataInterface ? $data : null;
	}

	public function __invoke(): PackageMetadataRegistryInterface
	{
		return self::make();
	}
}
