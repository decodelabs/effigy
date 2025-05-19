<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus\Session;

class Upgrade implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->newLine();

        // Update
        $this->io->info('Update composer');

        if (!Effigy::$project->run('update')) {
            return false;
        }

        $this->io->newLine();
        $this->io->newLine();


        // Veneer
        $this->io->info('Create veneer stubs');

        if (!Effigy::run('veneer-stub')) {
            return false;
        }

        return true;
    }
}
