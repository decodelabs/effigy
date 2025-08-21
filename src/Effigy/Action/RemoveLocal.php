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

class RemoveLocal implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $binFile = $this->effigy->project->rootDir->getFile('effigy');

        $this->io->{'brightMagenta'}('Deleting effigy executable... ');
        $binFile->delete();
        $this->io->{'success'}('done');

        $this->io->newLine();

        return $this->effigy->project->uninstallDev(...InstallLocal::Packages);
    }
}
