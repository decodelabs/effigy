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
        if (Cli::$command['global']) {
            Integra::forceLocal(false);
        }


        // Update
        if (
            !Integra::isForcedLocal() &&
            !Integra::runGlobal('update', '--with-all-dependencies')
        ) {
            return false;
        }


        // Install
        Cli::newLine();
        Cli::info('Re-installing effigy...');
        Cli::newLine();


        if (!Integra::installGlobal(
            Integra::preparePackageInstallName(
                'decodelabs/effigy',
                Cli::$command['dev'] ? 'dev-develop' : null
            )
        )) {
            return false;
        }


        // Exec
        if (Integra::isForcedLocal()) {
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
