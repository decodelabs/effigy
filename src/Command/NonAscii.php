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

class NonAscii implements Command
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

        $pathString = implode(' ', array_keys($dirs));
        $command = "! LC_ALL=C.UTF-8 find $pathString -type f -name \"*.php\" -print0 | xargs -0 -- grep -PHn \"[^ -~]\" | grep -v '// @ignore-non-ascii$'";
        $output = exec($command);

        if ($output !== '') {
            Cli::newLine();
            Cli::error('Non ascii:');
            Cli::write((string)$output);
            Cli::newLine();
        }

        return $output === '';
    }
}
