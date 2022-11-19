<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Terminus as Cli;

class Update implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        Cli::newLine();

        // Update
        Cli::info('Update composer');

        if (!$this->controller->run('composer', 'update')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();


        // Veneer
        Cli::info('Create veneer stubs');

        if (!$this->controller->run('veneer-stub')) {
            return false;
        }

        return true;
    }
}
