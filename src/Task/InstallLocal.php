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
use DecodeLabs\Terminus as Cli;

class InstallLocal implements Task
{
    public const PACKAGES = [
        'phpstan/phpstan',
        'decodelabs/effigy',
        'phpstan/extension-installer'
    ];

    public function execute(): bool
    {
        $binFile = Atlas::file(dirname(dirname(__DIR__)) . '/bin/effigy');
        $umask = umask(0);

        Cli::{'brightMagenta'}('Copying effigy executable... ');
        $target = $binFile->copyTo((string)Effigy::$rootDir);
        $target->setPermissions(0777);
        Cli::{'success'}('done');

        umask($umask);
        Cli::newLine();

        $args = ['composer', 'require', ...static::PACKAGES, '--dev'];
        return Effigy::run(...$args);
    }
}
