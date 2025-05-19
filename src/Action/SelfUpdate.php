<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus\Session;

#[Argument\Flag(
    name: 'dev',
    shortcut: 'd',
    description: 'Dev version from repo',
)]
#[Argument\Flag(
    name: 'global',
    shortcut: 'g',
    description: 'Force global',
)]
class SelfUpdate implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        // Local / global
        $global = !Effigy::isLocal();

        if ($request->parameters->getAsBool('global')) {
            $global = true;
        }


        // Update
        if (
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
        $this->io->newLine();
        $this->io->info('Re-installing effigy...');
        $this->io->newLine();

        $packageName = 'decodelabs/effigy';

        if ($request->parameters->getAsBool('dev')) {
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
            $this->io->newLine();
            $this->io->info('Re-installing local executable...');
            $this->io->newLine();

            if (!Effigy::run('install-local')) {
                return false;
            }
        }

        $this->io->newLine();
        $this->io->success('done');
        $this->io->newLine();

        return true;
    }
}
