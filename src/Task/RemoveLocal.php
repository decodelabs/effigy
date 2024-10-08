<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class RemoveLocal implements Task
{
    public function execute(): bool
    {
        $binFile = Integra::$rootDir->getFile('effigy');

        Cli::{'brightMagenta'}('Deleting effigy executable... ');
        $binFile->delete();
        Cli::{'success'}('done');

        Cli::newLine();

        return Integra::uninstallDev(...InstallLocal::Packages);
    }
}
