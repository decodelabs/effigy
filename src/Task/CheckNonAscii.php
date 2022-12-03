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
use DecodeLabs\Terminus as Cli;

class CheckNonAscii implements Task
{
    public function execute(): bool
    {
        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        chdir((string)Integra::$rootDir);

        $pathString = implode(' ', array_keys($dirs));
        $command = "! LC_ALL=C.UTF-8 find $pathString -type f -name \"*.php\" -not -name \"*.html.php\" -not -name \"*.htm.php\" -print0 | xargs -0 -- grep -PHn \"[^ -~]\" | grep -v '// @ignore-non-ascii$'";
        $output = exec($command);

        if ($output !== '') {
            Cli::error('Non ascii:');
            Cli::write((string)$output);
            Cli::newLine();
        } else {
            Cli::success('No non-ascii characters causing issues');
            Cli::newLine();
        }

        chdir((string)Integra::$runDir);

        return $output === '';
    }
}
