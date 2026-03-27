<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Factory;

use RuntimeException;
use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;
use SuperKernel\ComposerResolver\Package;
use SuperKernel\ComposerResolver\PackageCollector;
use SuperKernel\Contract\PackageCollectorInterface;
use SuperKernel\Contract\PackageInterface;
use SuperKernel\Contract\PathResolverInterface;
use SuperKernel\ProcessHandler\Contract\ProcessHandlerInterface;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function serialize;
use function str_replace;
use function unserialize;

final readonly class PackageCollectorFactory
{
	private string $vendorDir;

	private PathResolverInterface $cacheDir;

	public function __construct(
		private PathResolverInterface       $pathResolver,
		private ProcessHandlerInterface     $processHandler,
		private ComposerJsonReaderInterface $composerJsonReader,
		private ComposerLockReaderInterface $composerLockReader,
	)
	{
		$this->vendorDir = $this->composerJsonReader['config']['vendor-dir'] ?? 'vendor';
		$this->cacheDir = $pathResolver->to($this->vendorDir)->to('.super-kernel')->to('packages');

		$cacheDir = $this->cacheDir->get();
		if (!is_dir($cacheDir) && !mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
			throw new RuntimeException("Could not create cache dir: $cacheDir");
		}
	}

	public function create(): PackageCollectorInterface
	{
		$packages = [
			$this->getPackage([...$this->composerJsonReader]),
		];
		foreach (
			array_merge(
				$this->composerLockReader['packages'] ?? [],
				$this->composerLockReader['packages-dev'] ?? [],
			) as $packageRawData
		) {
			$package = $this->getPackage($packageRawData, $this->vendorDir);
			if (null === $package) {
				continue;
			}

			$packages[] = $package;
		}

		return new PackageCollector(...$packages);
	}

	private function getPackage(array $packageRawData, ?string $vendorDir = null): ?PackageInterface
	{
		$cacheFile = $this->getCacheFile($packageRawData['name'] ?? 'root');

		$this->processHandler->execute(function () use ($cacheFile, $packageRawData, $vendorDir) {
			$reference = $packageRawData['dist']['reference'] ?? null;
			$cachePackage = $this->loadPackage($cacheFile);

			if (null === $cachePackage?->getReference() || $cachePackage?->getReference() !== $reference) {
				$package = new PackageFactory($this->pathResolver, $packageRawData, $vendorDir)->create();

				file_put_contents($cacheFile, serialize($package));
			}
		});

		return $this->loadPackage($cacheFile);
	}

	private function loadPackage(string $cacheFile): ?PackageInterface
	{
		if (file_exists($cacheFile)) {
			$unserializedData = unserialize(
				data   : @file_get_contents($cacheFile),
				options: [
					         'allowed_classes' => [
						         Package::class,
					         ],
				         ],
			);
			if ($unserializedData instanceof PackageInterface) {
				return $unserializedData;
			}
		}

		return null;
	}

	private function getCacheFile(?string $packageName = null): string
	{
		$fileName = str_replace(['/', '\\'], '_', $packageName);

		return $this->cacheDir->to("$fileName.cache")->get();
	}
}