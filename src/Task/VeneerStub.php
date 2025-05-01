<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus as Cli;

class VeneerStub implements Task
{
    public function execute(): bool
    {
        $execFile = Effigy::$project->rootDir->getFile('vendor/bin/veneer-stub');

        if (!$execFile->exists()) {
            Cli::operative('This package does not use Veneer');
            return true;
        }

        return Effigy::$project->runBin('veneer-stub');
    }
}
