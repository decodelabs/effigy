<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use Composer\InstalledVersions;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Terminus\Session;

class Version implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->newLine();
        $this->io->{'brightCyan'}('Effigy ');
        $this->io->{'white'}(': ');
        $this->io->{'.brightYellow'}(InstalledVersions::getPrettyVersion('decodelabs/effigy'));
        $this->io->newLine();

        return true;
    }
}
