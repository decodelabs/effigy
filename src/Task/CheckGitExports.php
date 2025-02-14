<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Integra;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class CheckGitExports implements Task
{
    /**
     * @var array<string>
     */
    public const array ExcludeFiles = [
        'LICENSE',
        'README.md',
        'CHANGELOG.md',
        'composer.json',
        'composer.lock'
    ];

    public function execute(): bool
    {
        $dirs = Effigy::getCodeDirs();
        $exclude = [];

        foreach ($dirs as $name => $dir) {
            $exclude[] = '--exclude="' . $name . '"';
            $exclude[] = '--exclude="' . $name . '/*"';
        }

        if (Integra::$rootDir->getDir('bin')->exists()) {
            $exclude[] = '--exclude="bin"';
            $exclude[] = '--exclude="bin/*"';
        }

        foreach (self::ExcludeFiles as $file) {
            $exclude[] = '--exclude="' . $file . '"';
        }

        $result = Systemic::capture(
            'git archive HEAD | tar --list ' . implode(' ', $exclude),
            Integra::$rootDir->getPath()
        );

        if (!$result->wasSuccessful()) {
            Cli::error('Unable to capture export file list');
            return false;
        }

        $result = trim((string)$result->getOutput());

        if ($result === '') {
            Cli::success('No unexpected files exported');
            return true;
        }

        $files = explode("\n", $result);
        $exports = Effigy::getExportsWhitelist();
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
            Cli::error('Unexpected git export(s):');

            foreach ($output as $file) {
                Cli::write(' - ');
                Cli::{'.red'}($file);
            }

            return false;
        }

        Cli::success('All exports are accounted for');
        return true;
    }
}
