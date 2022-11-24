<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use Composer\InstalledVersions;
use DecodeLabs\Clip\Task;
use DecodeLabs\Terminus as Cli;

class Version implements Task
{
    public function execute(): bool
    {
        Cli::newLine();
        Cli::{'brightCyan'}('Effigy ');
        Cli::{'white'}(': ');
        Cli::{'.brightYellow'}(InstalledVersions::getVersion('decodelabs/effigy'));
        Cli::newLine();

        return true;
    }
}
