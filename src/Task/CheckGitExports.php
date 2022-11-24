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

        $result = Systemic::$process->launch(
            'git archive HEAD | tar --list ' . implode(' ', $exclude),
            [],
            Integra::$rootDir
        );

        if (!$result->wasSuccessful()) {
            return false;
        }

        $files = explode("\n", trim((string)$result->getOutput()));
        $exports = Effigy::getExportsWhitelist();
        $output = [];

        foreach ($files as $file) {
            $file = trim($file);


            if (!in_array($file, $exports)) {
                $output[] = $file;
            }
        }

        if (!empty($output)) {
            Cli::error('Unexpected git export(s):');

            foreach ($output as $file) {
                Cli::write(' - ');
                Cli::{'.red'}($file);
            }

            return false;
        }

        return true;
    }
}
