<?php

/**
 * Effigy
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class CheckExecutablePermissions implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
        protected Systemic $systemic,
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        $rootDir = $this->effigy->project->rootDir->path;

        $result = $this->systemic->capture(
            [
                'git',
                'ls-files',
                '--cached',
                '--others',
                '--exclude-standard',
                '-z',
            ],
            $rootDir
        );

        if (!$result->wasSuccessful()) {
            $this->io->error('Unable to capture executable file list');
            return false;
        }

        $result = rtrim((string)$result->getOutput(), "\0");
        $paths = $result === '' ? [] : explode("\0", $result);
        $bins = $this->effigy->project->getLocalManifest()->getBinFiles();
        $whitelist = $this->effigy->getExecutablesWhitelist();
        $output = [];
        $hasExecutables = false;

        foreach ($paths as $path) {
            if (
                $path === 'effigy' ||
                str_starts_with($path, 'vendor/') ||
                str_starts_with($path, '.effigy/') ||
                str_starts_with($path, 'node_modules/') ||
                str_contains($path, '/node_modules/')
            ) {
                continue;
            }

            $file = $rootDir . '/' . $path;

            if (
                is_link($file) ||
                !is_file($file) ||
                !is_executable($file)
            ) {
                continue;
            }

            $hasExecutables = true;

            if (
                !in_array($path, $bins) &&
                !in_array($path, $whitelist)
            ) {
                $output[] = $path;
            }
        }

        if (!$hasExecutables) {
            $this->io->success('No executable files found');
            return true;
        }

        if (!empty($output)) {
            $this->io->error('Unexpected executable file(s):');

            foreach ($output as $path) {
                $this->io->write(' - ');
                $this->io->{'.red'}($path);
            }

            return false;
        }

        $this->io->success('All executable files are expected');
        return true;
    }
}
