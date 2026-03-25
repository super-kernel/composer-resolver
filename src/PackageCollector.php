<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use RuntimeException;
use SuperKernel\Contract\PackageCollectorInterface;
use SuperKernel\Contract\PackageInterface;

final readonly class PackageCollector implements PackageCollectorInterface
{
	private PackageInterface $rootPackage;

	/**
	 * @var array<PackageInterface> $packages
	 */
	private array $packages;

	public function __construct(PackageInterface ...$packages)
	{
		$data = [];
		foreach ($packages as $package) {
			if ($package->getType() === PackageCollectorInterface::ROOT) {
				$this->rootPackage = $package;
			}
			$data[$package->getName()] = $package;
		}

		$this->packages = $data;
	}

	public function getPackagesByType(string $packageType): array
	{
		$packages = [];
		foreach ($this->packages as $package) {
			if ($packageType === $package->getType()) {
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

	public function getRootPackage(): PackageInterface
	{
		return $this->rootPackage;
	}

	public function getAllPackages(): array
	{
		return $this->packages;
	}
}