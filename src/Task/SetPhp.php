<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class SetPhp implements Task
{
    public function execute(): bool
    {
        $binName = (string)Cli::ask('Which PHP binary should this project use?', 'php');

        if (false === strpos($binName, '/')) {
            $binName = Systemic::$os->which($binName);
        }

        Cli::{'.brightYellow'}($binName);

        // TODO: validate binary

        Effigy::$config->set('php', $binName);
        return true;
    }
}
