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

class SelfUpdate implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (
            !$this->controller->isLocal() &&
            !$this->controller->run('composer', 'global', 'update')
        ) {
            return false;
        }

        Cli::newLine();
        Cli::info('Re-requiring effigy...');
        Cli::newLine();

        if (!$this->controller->run('composer', 'global', 'require', 'decodelabs/effigy')) {
            return false;
        }



        if ($this->controller->isLocal()) {
            Cli::newLine();
            Cli::info('Re-installing local executable...');
            Cli::newLine();

            if (!$this->controller->run('install-local')) {
                return false;
            }
        }

        Cli::newLine();
        Cli::success('done');
        Cli::newLine();

        return true;
    }
}
