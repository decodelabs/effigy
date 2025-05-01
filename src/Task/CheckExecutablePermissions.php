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

class CheckExecutablePermissions implements Task
{
    public function execute(): bool
    {
        $exclude = [
            './.git/*',
            './vendor/*',
            './effigy',
            '*/node_modules/*',
        ];

        $exStr = [];

        foreach ($exclude as $glob) {
            $exStr[] = '-not -path \'' . $glob . '\'';
        }

        $result = Systemic::capture(
            'find . -type f \\( -perm -u=x -o -perm -g=x -o -perm -o=x \\) ' . implode(' ', $exStr) . ' -exec test -x {} \\; -print',
            Effigy::$project->rootDir->getPath()
        );

        if (!$result->wasSuccessful()) {
            Cli::error('Unable to capture executable file list');
            return false;
        }

        $result = trim((string)$result->getOutput());

        if ($result === '') {
            Cli::success('No executable files found');
            return true;
        }

        $paths = explode("\n", $result);
        $bins = Effigy::$project->getLocalManifest()->getBinFiles();
        $whitelist = Effigy::getExecutablesWhitelist();
        $output = [];

        foreach ($paths as $path) {
            $path = substr($path, 2);

            if (
                !in_array($path, $bins) &&
                !in_array($path, $whitelist)
            ) {
                $output[] = $path;
            }
        }

        if (!empty($output)) {
            Cli::error('Unexpected executable file(s):');

            foreach ($output as $path) {
                Cli::write(' - ');
                Cli::{'.red'}($path);
            }

            return false;
        }

        Cli::success('All executable files are expected');
        return true;
    }
}
