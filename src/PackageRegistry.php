<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use RuntimeException;
use SuperKernel\ComposerResolver\Contract\ComposerJsonReaderInterface;
use SuperKernel\ComposerResolver\Contract\ComposerLockReaderInterface;
use SuperKernel\ComposerResolver\Contract\PackageInterface;
use SuperKernel\ComposerResolver\Contract\PackageRegistryInterface;
use SuperKernel\ComposerResolver\Enum\PackageTypeEnum;
use SuperKernel\PathResolver\Provider\PathResolverProvider;

final readonly class PackageRegistry implements PackageRegistryInterface
{
	/**
	 * @var array<PackageInterface> $packages
	 */
	private array $packages;

	public function __construct(
		private ComposerJsonReaderInterface $composerJsonReader,
		private ComposerLockReaderInterface $composerLockReader,
	)
	{
		$packages = [
			$this->composerJsonReader->getName() => new Package(PathResolverProvider::make(), ...$this->composerJsonReader->toArray()),
		];
		foreach (array_merge(
			         $this->composerLockReader->getPackages(),
			         $this->composerLockReader->getPackagesDev(),
		         ) as $data) {
			$package = new Package(
				   PathResolverProvider::make()->to($this->composerJsonReader->getConfig('vendor-dir') ?? 'vendor')->to($data['name']),
				...$data,
			);


			$packages[$package->getName()] = $package;
		}

		$this->packages = $packages;
	}

	public function getPackages(): array
	{
		return $this->packages;
	}

	public function getPackagesByType(PackageTypeEnum $packageType): array
	{
		$packages = [];
		foreach ($this->packages as $package) {
			if ($packageType->value === $package->getType()) {
				$packages[] = $package;
			}
		}
		return $packages;
	}

	public function getPackage(string $packageName): PackageInterface
	{
		if ($this->hasPackage($packageName)) {
			return $this->packages[$packageName];
		}

		throw new RuntimeException("Package '$packageName' not found");
	}

	public function hasPackage(string $packageName): bool
	{
		return isset($this->packages[$packageName]);
	}
}