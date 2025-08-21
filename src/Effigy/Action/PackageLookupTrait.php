<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Effigy;
use DecodeLabs\Exceptional;
use DecodeLabs\Integra\Structure\Package;

trait PackageLookupTrait
{
    protected Effigy $effigy;

    /**
     * @var array<string, Package>
     */
    private array $require;

    /**
     * @var array<string, Package>
     */
    private array $requireDev;

    /**
     * @return array<string, Package>
     */
    protected function getPackages(): array
    {
        if (!isset($this->require)) {
            $this->require = $this->effigy->project->getLocalManifest()->getRequiredPackages();
        }

        return $this->require;
    }

    /**
     * @return array<string, Package>
     */
    protected function getDevPackages(): array
    {
        if (!isset($this->requireDev)) {
            $this->requireDev = $this->effigy->project->getLocalManifest()->getRequiredDevPackages();
        }

        return $this->requireDev;
    }

    /**
     * @return array<string, Package>
     */
    protected function getAllPackages(): array
    {
        return array_merge(
            $this->getPackages(),
            $this->getDevPackages()
        );
    }

    /**
     * @param array<string> $packages
     * @return array<string, Package>
     */
    protected function lookupPackages(
        array $packages,
        bool $allowUnknown = false
    ): array {
        $output = [];

        foreach ($packages as $package) {
            if (preg_match('|^[a-zA-Z0-9-_]+/\*$|', $package)) {
                $output = array_merge($output, $this->lookupPackageGroup($package));
                continue;
            }

            $package = $this->lookupPackage($package, $allowUnknown);
            $output[$package->name] = $package;
        }

        return $output;
    }

    protected function lookupPackage(
        string $key,
        bool $allowUnknown = false
    ): Package {
        foreach ($this->getAllPackages() as $name => $package) {
            if (
                $name === $key ||
                str_ends_with($name, '/' . $key)
            ) {
                return $package;
            }
        }

        if (
            $allowUnknown &&
            str_contains($key, '/')
        ) {
            return new Package($key, 'dev-develop');
        }

        throw Exceptional::InvalidArgument(
            message: 'Unable to resolve package: ' . $key
        );
    }

    /**
     * @return array<string, Package>
     */
    protected function lookupPackageGroup(
        string $group
    ): array {
        $output = [];
        $group = substr($group, 0, -1);

        foreach ($this->getAllPackages() as $name => $package) {
            if (str_starts_with($name, $group)) {
                $output[$name] = $package;
            }
        }

        return $output;
    }

    protected function isDevPackage(
        string $package
    ): bool {
        return isset($this->requireDev[$package]);
    }
}
