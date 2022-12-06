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
use DecodeLabs\Integra;
use DecodeLabs\Integra\Structure\Package;
use DecodeLabs\Terminus as Cli;

class Mount implements Task
{
    use PackageLookupTrait;

    /**
     * @var array<string>
     */
    protected array $roots = [];

    public function execute(): bool
    {
        Cli::getCommandDefinition()
            ->addArgument('packages*', 'Package names');

        Cli::prepareArguments();

        $conf = Integra::getLocalManifest()->getRepositoryConfig();
        /** @var array<string> $packages */
        $packages = Cli::getArgument('packages');
        $packages = $this->lookupPackages($packages);
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

                if (!Integra::run('config', 'repositories.' . $key, '{"type": "path", "url": "' . $path . '", "options": {"symlink": true}}')) {
                    return false;
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

        clearstatcache();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return true;
    }

    protected function getPath(Package $package): string
    {
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

    protected function findPath(Package $package): string
    {
        $keyName = explode('/', $package->name, 2)[1] ?? null;

        if (
            $keyName !== null &&
            !empty($this->roots)
        ) {
            foreach ($this->roots as $root) {
                if (str_starts_with($root, '/')) {
                    $file = Atlas::file($root . '/' . $keyName . '/composer.json');
                } else {
                    $file = Integra::$rootDir->getFile($root . '/' . $keyName . '/composer.json');
                }

                if ($file->exists()) {
                    return $root . '/' . $keyName;
                }
            }
        }

        return (string)Cli::ask(
            'Where is your local copy of ' . $package->name . ' located?',
            null,
            function ($path, $session) {
                if (str_starts_with($path, '/')) {
                    $file = Atlas::file($path . '/composer.json');
                } else {
                    $file = Integra::$rootDir->getFile($path . '/composer.json');
                }

                if (!$file->exists()) {
                    $session->error('Path "' . $path . '" does not exist');
                    return false;
                }
            }
        );
    }
}
