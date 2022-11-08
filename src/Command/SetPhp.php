<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class SetPhp implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): void
    {
        $binName = (string)Cli::ask('Which PHP binary should this project use?', 'php');

        if (false === strpos($binName, '/')) {
            $binName = Systemic::$os->which($binName);
        }

        Cli::{'.brightYellow'}($binName);

        // TODO: validate binary

        $this->controller->setConfig('php', $binName);
    }
}
