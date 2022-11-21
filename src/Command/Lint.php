<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;

class Lint implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $dirs = $this->controller->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);
        return $this->controller->run('composer', 'global', 'exec', 'parallel-lint', ...$paths);
    }
}
