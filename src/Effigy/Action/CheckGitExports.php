<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class CheckGitExports implements Action
{
    /**
     * @var array<string>
     */
    public const array ExcludeFiles = [
        'LICENSE',
        'README.md',
        'CHANGELOG.md',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'pnpm-lock.yaml',
        'pnpm-workspace.yaml',
        'yarn.lock',
        '.npmrc'
    ];

    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
        protected Systemic $systemic,
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        $dirs = $this->effigy->getCodeDirs();
        $exclude = [];

        foreach ($dirs as $name => $dir) {
            $exclude[] = '--exclude="' . $name . '"';
            $exclude[] = '--exclude="' . $name . '/*"';
        }

        if ($this->effigy->project->rootDir->getDir('bin')->exists()) {
            $exclude[] = '--exclude="bin"';
            $exclude[] = '--exclude="bin/*"';
        }

        foreach (self::ExcludeFiles as $file) {
            $exclude[] = '--exclude="' . $file . '"';
        }

        $result = $this->systemic->capture(
            'git archive HEAD | tar --list ' . implode(' ', $exclude),
            $this->effigy->project->rootDir->path
        );

        if (!$result->wasSuccessful()) {
            $this->io->error('Unable to capture export file list');
            return false;
        }

        $result = trim((string)$result->getOutput());

        if ($result === '') {
            $this->io->success('No unexpected files exported');
            return true;
        }

        $files = explode("\n", $result);
        $exports = $this->effigy->getExportsWhitelist();
        $output = [];

        foreach ($files as $file) {
            $file = trim($file);

            if (str_ends_with($file, '/')) {
                continue;
            }

            foreach ($exports as $export) {
                if (
                    $file === $export ||
                    str_starts_with($file, $export . '/')
                ) {
                    continue 2;
                }
            }

            $output[] = $file;
        }

        if (!empty($output)) {
            $this->io->error('Unexpected git export(s):');

            foreach ($output as $file) {
                $this->io->write(' - ');
                $this->io->{'.red'}($file);
            }

            return false;
        }

        $this->io->success('All exports are accounted for');
        return true;
    }
}
