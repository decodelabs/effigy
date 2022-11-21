<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class CheckGitExports implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $dirs = $this->controller->getCodeDirs();
        $exclude = [];

        foreach ($dirs as $name => $dir) {
            $exclude[] = '--exclude="' . $name . '"';
            $exclude[] = '--exclude="' . $name . '/*"';
        }

        if ($this->controller->rootDir->getDir('bin')->exists()) {
            $exclude[] = '--exclude="bin"';
            $exclude[] = '--exclude="bin/*"';
        }

        $result = Systemic::$process->launch(
            'git archive HEAD | tar --list ' . implode(' ', $exclude),
            [],
            $this->controller->rootDir
        );

        if (!$result->wasSuccessful()) {
            return false;
        }

        $files = explode("\n", trim((string)$result->getOutput()));
        $exports = $this->controller->getExportsWhitelist();
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
