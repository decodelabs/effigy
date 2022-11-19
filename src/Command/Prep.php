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

class Prep implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        // Update
        if (!$this->controller->run('update')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();


        // Analysis
        Cli::info('Clear PHPStan cache');

        if (!$this->controller->run('analyze', '--clear')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();

        Cli::info('Run PHPStan analysis');

        if (!$this->controller->run('analyze')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Standards
        Cli::info('Ensure proper code formatting');

        if (!$this->controller->run('format')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();

        // Lint
        Cli::info('Ensure all files are syntax compliant');

        if (!$this->controller->run('lint')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // EC Lint
        Cli::info('Check for editorconfig issues');

        if (!$this->controller->run('eclint')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Non ascii
        Cli::info('Check for non-ASCII characters');

        if (!$this->controller->run('non-ascii')) {
            return false;
        }

        return true;
    }
}
