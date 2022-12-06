<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Integra;
use DecodeLabs\Integra\Structure\Package;
use DecodeLabs\Terminus as Cli;
use Throwable;

class Unmount implements Task
{
    use PackageLookupTrait;

    /**
     * @var Tree<float|int|string|null>
     */
    protected Tree $repositories;

    public function execute(): bool
    {
        Cli::getCommandDefinition()
            ->addArgument('?packages*', 'Package name')
            ->addArgument('--all', 'All packages');

        Cli::prepareArguments();

        $this->repositories = Integra::getLocalManifest()->getRepositoryConfig();

        if (
            Cli::getArgument('all') ||
            Cli::getArgument('packages') === null
        ) {
            $packages = $this->lookupAllPackages();
        } else {
            /** @var array<string> $packages */
            $packages = Cli::getArgument('packages');
            $packages = $this->lookupPackages($packages);
        }


        $requires = $devRequires = [];

        foreach ($packages as $package) {
            Integra::run('config', '--unset', 'repositories.local:' . $package->name);

            if (str_ends_with($package->version, '@dev')) {
                $version = substr($package->version, 0, -4);
                $require = $package->name . ':' . $version;

                if ($this->isDevPackage($package->name)) {
                    $devRequires[] = $require;
                } else {
                    $requires[] = $require;
                }
            }
        }

        if (
            !empty($requires) &&
            !Integra::run(...['require', ...$requires, '--no-update'])
        ) {
            return false;
        }

        if (
            !empty($devRequires) &&
            !Integra::run(...['require', ...$devRequires, '--dev', '--no-update'])
        ) {
            return false;
        }

        Integra::run('update');
        return true;
    }

    /**
     * @return array<string, Package>
     */
    protected function lookupAllPackages(): array
    {
        $output = [];

        foreach ($this->repositories as $key => $config) {
            if (!str_starts_with((string)$key, 'local:')) {
                continue;
            }

            $name = substr((string)$key, 6);

            try {
                $output[$name] = $this->lookupPackage($name);
            } catch (Throwable $e) {
            }
        }

        return $output;
    }
}
