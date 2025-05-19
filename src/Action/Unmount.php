<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Collections\Tree;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Integra\Structure\Package;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;
use Throwable;

#[Argument\Flag(
    name: 'global',
    shortcut: 'g',
    description: 'Mount globally'
)]
#[Argument\ValueList(
    name: 'packages',
    description: 'Package name',
)]
#[Argument\Flag(
    name: 'all',
    shortcut: 'a',
    description: 'All packages'
)]
class Unmount implements Action
{
    use PackageLookupTrait;

    /**
     * @var Tree<string|int|float|bool>
     */
    protected Tree $repositories;

    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $packages = $request->parameters->getAsStringList('packages');
        $all = $request->parameters->getAsBool('all');

        if ($request->parameters->getAsBool('global')) {
            return $this->runGlobal($packages, $all);
        }

        $this->repositories = Effigy::$project->getLocalManifest()->getRepositoryConfig();

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
            Effigy::$project->run('config', '--unset', 'repositories.local:' . $package->name);

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
            !Effigy::$project->run(...['remove', ...$removes, '--no-update'])
        ) {
            return false;
        }

        if (
            !empty($requires) &&
            !Effigy::$project->run(...['require', ...$requires, '--no-update'])
        ) {
            return false;
        }

        if (
            !empty($devRequires) &&
            !Effigy::$project->run(...['require', ...$devRequires, '--dev', '--no-update'])
        ) {
            return false;
        }

        if (
            !empty($requires) ||
            !empty($devRequires) ||
            !empty($removes)
        ) {
            Effigy::$project->run('update');
        } else {
            $this->io->operative('No packages mounted');
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
