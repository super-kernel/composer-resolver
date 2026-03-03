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
use SuperKernel\ComposerResolver\Contract\ScannerInterface;
use SuperKernel\ComposerResolver\Factory\PackageMetadataFactory;
use SuperKernel\ComposerResolver\Factory\ScannerFactory;
use SuperKernel\ComposerResolver\PackageMetadata;
use SuperKernel\ComposerResolver\PackageMetadataRegistry;
use SuperKernel\PathResolver\Contract\PathResolverInterface;
use SuperKernel\PathResolver\Provider\PathResolverProvider;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function rename;
use function serialize;
use function str_replace;
use function strlen;
use function uniqid;
use function unserialize;

#[
	Provider(PackageMetadataRegistryInterface::class),
	Factory,
]
final class PackageMetadataRegistryProvider
{
	private static PackageMetadataRegistryInterface $packageMetadataRegistry;

	private ScannerInterface $scanner;

	private PathResolverInterface $pathResolver;

	private PackageMetadataFactory $packageMetadataFactory;

	public function __construct()
	{
		$this->scanner = ScannerFactory::make();
		$this->pathResolver = PathResolverProvider::make()->to('vendor')->to('.super-kernel')->to('package-metadata');
		$this->packageMetadataFactory = new PackageMetadataFactory();

		$dir = $this->pathResolver->get();
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new RuntimeException("Could not create cache dir: $dir");
		}
	}

	public function __invoke(): PackageMetadataRegistryInterface
	{
		if (!isset(self::$packageMetadataRegistry)) {
			$packageRegistry = PackageRegistryProvider::make();

			$packagesMetadata = [];
			foreach ($packageRegistry->getPackages() as $package) {
				$packagesMetadata[] = $this->resolveMetadata($package);
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

	private function resolveMetadata(PackageInterface $package): PackageMetadataInterface
	{
		$fileName = str_replace(['/', '\\'], '_', $package->getName());
		$filePath = $this->pathResolver->to($fileName)->get();

		$cached = $this->loadCache($filePath);
		$isPhar = strlen(Phar::running(false)) > 0;

		$needsScan = false;
		if (!$cached) {
			$needsScan = true;
		} elseif (!$isPhar) {
			$currentRef = $package->getReference();
			if ($currentRef === null || $cached->getReference() !== $currentRef) {
				$needsScan = true;
			}
		}

		if ($needsScan) {
			$this->scanner->execute(function () use ($package, $filePath) {
				$metadata = $this->packageMetadataFactory->make($package);
				$this->atomicWrite($filePath, $metadata);
			});
			return $this->loadCache($filePath) ?? throw new RuntimeException("Scan failed for {$package->getName()}");
		}

		return $cached;
	}

	private function atomicWrite(string $path, PackageMetadataInterface $data): void
	{
		$temp = $path . '.' . uniqid('', true) . '.tmp';
		file_put_contents($temp, serialize($data), LOCK_EX);
		rename($temp, $path);
	}

	private function loadCache(string $path): ?PackageMetadataInterface
	{
		if (!is_file($path)) return null;
		$content = file_get_contents($path);
		if (!$content) return null;

		$data = unserialize($content, ['allowed_classes' => [PackageMetadata::class]]);
		return $data instanceof PackageMetadataInterface ? $data : null;
	}
}
