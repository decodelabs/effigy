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
        protected Effigy $effigy,
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        // Local / global
        $global = !$this->effigy->local;

        if ($request->parameters->asBool('global')) {
            $global = true;
        }


        // Update
        if (
            (
                $global &&
                !$this->effigy->project->runGlobal('update', '--with-all-dependencies')
            ) ||
            (
                !$global &&
                !$this->effigy->project->run('update', '--with-all-dependencies')
            )
        ) {
            return false;
        }


        // Install
        $this->io->newLine();
        $this->io->info('Re-installing effigy...');
        $this->io->newLine();

        $packageName = 'decodelabs/effigy';

        if ($request->parameters->asBool('dev')) {
            $packageName .= ':dev-develop';
        }

        if (
            (
                $global &&
                !$this->effigy->project->installGlobal($packageName)
            ) ||
            (
                !$global &&
                !$this->effigy->project->install($packageName)
            )
        ) {
            return false;
        }


        // Exec
        if ($this->effigy->local) {
            $this->io->newLine();
            $this->io->info('Re-installing local executable...');
            $this->io->newLine();

            if (!$this->effigy->run('install-local')) {
                return false;
            }
        }

        $this->io->newLine();
        $this->io->success('done');
        $this->io->newLine();

        return true;
    }
}
