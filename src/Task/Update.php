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

class Update implements Task
{
    public function execute(): bool
    {
        Cli::newLine();

        // Update
        Cli::info('Update composer');

        if (!Integra::run('update')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();


        // Veneer
        Cli::info('Create veneer stubs');

        if (!Effigy::run('veneer-stub')) {
            return false;
        }

        return true;
    }
}
