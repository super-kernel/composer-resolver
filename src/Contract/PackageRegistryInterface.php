<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

use SuperKernel\ComposerResolver\Enum\PackageTypeEnum;

interface PackageRegistryInterface
{
	/**
	 * @return array<PackageInterface>
	 */
	public function getPackages(): array;

	public function getPackagesByType(PackageTypeEnum $packageType): array;

	public function getPackage(string $packageName): PackageInterface;

	public function hasPackage(string $packageName): bool;
}