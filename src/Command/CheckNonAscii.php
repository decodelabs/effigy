<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Terminus as Cli;

class CheckNonAscii implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $dirs = $this->controller->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        chdir((string)$this->controller->rootDir);

        $pathString = implode(' ', array_keys($dirs));
        $command = "! LC_ALL=C.UTF-8 find $pathString -type f -name \"*.php\" -print0 | xargs -0 -- grep -PHn \"[^ -~]\" | grep -v '// @ignore-non-ascii$'";
        $output = exec($command);

        if ($output !== '') {
            Cli::error('Non ascii:');
            Cli::write((string)$output);
            Cli::newLine();
        } else {
            Cli::success('No non-ascii characters causing issues');
            Cli::newLine();
        }

        chdir((string)$this->controller->runDir);

        return $output === '';
    }
}
