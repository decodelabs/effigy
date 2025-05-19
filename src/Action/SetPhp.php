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
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class SetPhp implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $binName = (string)$this->io->ask('Which PHP binary should this project use?', 'php');

        if (false === strpos($binName, '/')) {
            $binName = Systemic::$os->which($binName);
        }

        $this->io->{'.brightYellow'}($binName);

        // TODO: validate binary

        Effigy::$config->set('php', $binName);
        return true;
    }
}
