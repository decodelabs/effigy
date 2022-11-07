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
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class InstallLocal implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): void
    {
        $binFile = Atlas::file(dirname(dirname(__DIR__)) . '/bin/effigy');
        $umask = umask(0);

        Cli::{'brightMagenta'}('Copying effigy executable... ');
        $target = $binFile->copyTo((string)$this->controller->rootDir);
        $target->setPermissions(0777);
        Cli::{'success'}('done');

        umask($umask);

        Cli::newLine();
        $user = Systemic::$process->getCurrent()->getOwnerName();
        $args = ['require', 'decodelabs/effigy'];

        Systemic::$process->newLauncher('composer', $args, null, null, $user)
            ->setSession(Cli::getSession())
            ->launch();
    }
}
