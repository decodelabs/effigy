<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Integra;
use Decodelabs\Systemic;
use DecodeLabs\Terminus as Cli;

class CheckExecutablePermissions implements Task
{
    public function execute(): bool
    {
        $exclude = [
            './.git/*',
            './vendor/*',
            './effigy'
        ];

        $exStr = [];

        foreach ($exclude as $glob) {
            $exStr[] = '-not -path \'' . $glob . '\'';
        }

        $result = Systemic::capture(
            'find . -type f ' . implode(' ', $exStr) . ' -executable',
            Integra::$rootDir
        );

        if (!$result->wasSuccessful()) {
            return false;
        }

        $paths = explode("\n", trim((string)$result->getOutput()));
        $bins = Integra::getLocalManifest()->getBinFiles();
        $output = [];

        foreach ($paths as $path) {
            $path = substr($path, 2);

            if (!in_array($path, $bins)) {
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

        return true;
    }
}
