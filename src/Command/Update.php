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

    public function execute(): void
    {
        $this->controller->run('composer', 'update');

        if ($this->controller->canRun('analyze')) {
            $this->controller->run('analyze', '--clear');
            $this->controller->run('analyze');
        }

        if ($this->controller->canRun('ecs-fix')) {
            $this->controller->run('ecs-fix');
        }

        if ($this->controller->canRun('lint')) {
            $this->controller->run('lint');
        }

        if ($this->controller->canRun('eclint-fix')) {
            $this->controller->run('eclint-fix');
        }

        if ($this->controller->canRun('non-ascii')) {
            $this->controller->run('non-ascii');
        }
    }
}
