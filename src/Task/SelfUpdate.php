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

class SelfUpdate implements Task
{
    public function execute(): bool
    {
        Cli::$command
            ->addArgument('-dev|d', 'Dev version from repo')
            ->addArgument('-global|g', 'Force global');


        // Local / global
        $global = !Effigy::isLocal();

        if (Cli::$command['global']) {
            $global = true;
        }


        // Update
        if(
            (
                $global &&
                !Effigy::$project->runGlobal('update', '--with-all-dependencies')
            ) ||
            (
                !$global &&
                !Effigy::$project->run('update', '--with-all-dependencies')
            )
        ) {
            return false;
        }


        // Install
        Cli::newLine();
        Cli::info('Re-installing effigy...');
        Cli::newLine();

        $packageName = 'decodelabs/effigy';

        if (Cli::$command['dev']) {
            $packageName .= ':dev-develop';
        }

        if (
            (
                $global &&
                !Effigy::$project->installGlobal($packageName)
            ) ||
            (
                !$global &&
                !Effigy::$project->install($packageName)
            )
        ) {
            return false;
        }


        // Exec
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
