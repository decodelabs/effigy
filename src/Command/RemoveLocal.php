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

class RemoveLocal implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $binFile = $this->controller->rootDir->getFile('effigy');

        Cli::{'brightMagenta'}('Deleting effigy executable... ');
        $binFile->delete();
        Cli::{'success'}('done');

        Cli::newLine();
        $args = ['remove', 'decodelabs/effigy'];

        return $this->controller->newComposerLauncher($args)
            ->launch()
            ->wasSuccessful();
    }
}
