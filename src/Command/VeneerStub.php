<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;

class VeneerStub implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $execFile = $this->controller->rootDir->getFile('vendor/bin/veneer-stub');

        if (!$execFile->exists()) {
            return true;
        }

        return $this->controller->run('composer', 'exec', 'veneer-stub');
    }
}
