<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver;

use RuntimeException;
use SuperKernel\ComposerResolver\Contract\PackageInterface;
use SuperKernel\ComposerResolver\Contract\PackageRegistryInterface;
use SuperKernel\ComposerResolver\Enum\PackageTypeEnum;

final readonly class PackageRegistry implements PackageRegistryInterface
{
	/**
	 * @param array $packages
	 */
	public function __construct(private array $packages)
	{
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