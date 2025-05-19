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

class VeneerStub implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $execFile = Effigy::$project->rootDir->getFile('vendor/bin/veneer-stub');

        if (!$execFile->exists()) {
            $this->io->operative('This package does not use Veneer');
            return true;
        }

        return Effigy::$project->runBin('veneer-stub');
    }
}
