<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;

class Update implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        // Update
        if (!$this->controller->run('composer', 'update')) {
            return false;
        }


        // Analysis
        if (!$this->controller->run('analyze', '--clear')) {
            return false;
        }

        if (!$this->controller->run('analyze')) {
            return false;
        }


        // Standards
        if (!$this->controller->run('format')) {
            return false;
        }


        // Lint
        if (!$this->controller->run('lint')) {
            return false;
        }


        // EC Lint
        if (!$this->controller->run('eclint')) {
            return false;
        }


        // Non ascii
        if (!$this->controller->run('non-ascii')) {
            return false;
        }

        return true;
    }
}
