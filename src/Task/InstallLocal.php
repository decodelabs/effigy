<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas;
use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class InstallLocal implements Task
{
    /**
     * @var array<string>
     */
    public const array Packages = [
        'phpstan/phpstan',
        'decodelabs/effigy',
        'phpstan/extension-installer'
    ];

    public function execute(): bool
    {
        $binFile = Atlas::file(dirname(dirname(__DIR__)) . '/bin/effigy');
        $umask = umask(0);

        Cli::{'brightMagenta'}('Copying effigy executable... ');
        $target = $binFile->copyTo(Effigy::$project->rootDir->getPath());
        $target->setPermissions(0777);
        Cli::{'success'}('done');

        umask($umask);
        Cli::newLine();

        return Effigy::$project->installDev(...static::Packages);
    }
}
