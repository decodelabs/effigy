<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas;
use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Integra\Structure\Package;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;
use DecodeLabs\Terminus\Session;

class Mount implements Task
{
    use PackageLookupTrait;

    /**
     * @var array<string>
     */
    protected array $roots = [];

    public function execute(): bool
    {
        Cli::$command
            ->addArgument('--global|g', 'Mount globally')
            ->addArgument('packages[]', 'Package names');

        $conf = Effigy::$project->getLocalManifest()->getRepositoryConfig();
        /** @var array<string> $packages */
        $packages = Cli::$command['packages'];

        if (Cli::$command['global']) {
            return $this->runGlobal($packages);
        }

        $packages = $this->lookupPackages($packages, true);
        $requires = $devRequires = [];

        if (empty($packages)) {
            Cli::warning('No packages specified');
            return true;
        }

        foreach ($packages as $name => $package) {
            $key = 'local:' . $package->name;
            $require = $package->name . ':' . $package->version;

            if (!str_ends_with($require, '@dev')) {
                $require .= '@dev';
            }

            if ($this->isDevPackage($package->name)) {
                $devRequires[] = $require;
            } else {
                $requires[] = $require;
            }

            if (!isset($conf->{$key})) {
                $path = $this->getPath($package);

                if (!Effigy::$project->run('config', 'repositories.' . $key, '{"type": "path", "url": "' . $path . '", "options": {"symlink": true}}')) {
                    return false;
                }
            }
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

        Effigy::$project->run('update');

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
        array $packages
    ): bool {
        $path = Effigy::getGlobalPath();

        return Systemic::run([
            'effigy', 'mount', ...$packages
        ], $path);
    }

    protected function getPath(
        Package $package
    ): string {
        $local = Effigy::$config->getLocalRepos();

        if (isset($local[$package->name])) {
            return $local[$package->name];
        }

        $path = $this->findPath($package);
        $root = dirname($path);

        if (!in_array($root, $this->roots)) {
            $this->roots[] = $root;
        }

        Effigy::$config->set('localRepos', [
            $package->name => $path
        ]);

        return $path;
    }

    protected function findPath(
        Package $package
    ): string {
        $keyName = explode('/', $package->name, 2)[1] ?? null;

        if (
            $keyName !== null &&
            !empty($this->roots)
        ) {
            foreach ($this->roots as $root) {
                if (str_starts_with($root, '/')) {
                    $file = Atlas::file($root . '/' . $keyName . '/composer.json');
                } else {
                    $file = Effigy::$project->rootDir->getFile($root . '/' . $keyName . '/composer.json');
                }

                if ($file->exists()) {
                    return $root . '/' . $keyName;
                }
            }
        }

        return (string)Cli::ask(
            message: 'Where is your local copy of ' . $package->name . ' located?',
            validator: function (
                string $path,
                Session $session
            ): bool {
                if (str_starts_with($path, '/')) {
                    $file = Atlas::file($path . '/composer.json');
                } else {
                    $file = Effigy::$project->rootDir->getFile($path . '/composer.json');
                }

                if (!$file->exists()) {
                    $session->error('Path "' . $path . '" does not exist');
                    return false;
                }

                return true;
            }
        );
    }
}
