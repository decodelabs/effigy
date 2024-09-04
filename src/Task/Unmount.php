<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Effigy;
use DecodeLabs\Integra;
use DecodeLabs\Integra\Structure\Package;
use DecodeLabs\Systemic;
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
        Cli::$command
            ->addArgument('--global|g', 'Mount globally')
            ->addArgument('?packages*', 'Package name')
            ->addArgument('--all', 'All packages');

        /** @var array<string>|null $packages */
        $packages = Cli::$command['packages'];
        $all = Coercion::toBool(Cli::$command['all']);

        if (Cli::$command['global']) {
            return $this->runGlobal($packages, $all);
        }

        $this->repositories = Integra::getLocalManifest()->getRepositoryConfig();

        if (
            $all ||
            empty($packages)
        ) {
            $packages = $this->lookupAllPackages();
        } else {
            $packages = $this->lookupPackages($packages);
        }


        $requires = $devRequires = $removes = [];

        foreach ($packages as $package) {
            Integra::run('config', '--unset', 'repositories.local:' . $package->name);

            if (!str_ends_with($package->version, '@dev')) {
                continue;
            }

            if ($package->version === 'dev-develop@dev') {
                $removes[] = $package->name;
            } else {
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
            !empty($removes) &&
            !Integra::run(...['remove', ...$removes, '--no-update'])
        ) {
            return false;
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

        if (
            !empty($requires) ||
            !empty($devRequires) ||
            !empty($removes)
        ) {
            Integra::run('update');
        } else {
            Cli::operative('No packages mounted');
        }

        clearstatcache();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return true;
    }

    /**
     * @param array<string> $packages
     */
    protected function runGlobal(
        ?array $packages,
        bool $all
    ): bool {
        $path = Effigy::getGlobalPath();
        $args = [
            'effigy', 'unmount'
        ];

        if (
            empty($packages) ||
            $all
        ) {
            $args[] = '--all';
        } else {
            $args = array_merge($args, $packages);
        }

        return Systemic::run($args, $path);
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
