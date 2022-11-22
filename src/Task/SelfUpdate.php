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

class SelfUpdate implements Task
{
    public function execute(): bool
    {
        if (
            !Effigy::isLocal() &&
            !Effigy::run('composer', 'global', 'update')
        ) {
            return false;
        }

        Cli::newLine();
        Cli::info('Re-requiring effigy...');
        Cli::newLine();

        if (!Effigy::run('composer', 'global', 'require', 'decodelabs/effigy')) {
            return false;
        }



        if (Effigy::isLocal()) {
            Cli::newLine();
            Cli::info('Re-installing local executable...');
            Cli::newLine();

            if (!Effigy::run('install-local')) {
                return false;
            }
        }

        Cli::newLine();
        Cli::success('done');
        Cli::newLine();

        return true;
    }
}
