<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas;
use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Terminus as Cli;

class Composer implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): void
    {
        $args = Cli::prepareArguments();
        array_shift($args);

        $this->controller->newComposerLauncher($args)
            ->launch();
    }
}
