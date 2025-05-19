<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus\Session;

class InstallLocal implements Action
{
    /**
     * @var array<string>
     */
    public const array Packages = [
        'phpstan/phpstan',
        'decodelabs/effigy',
        'phpstan/extension-installer'
    ];

    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $binFile = Atlas::file(dirname(dirname(__DIR__)) . '/bin/effigy');
        $umask = umask(0);

        $this->io->{'brightMagenta'}('Copying effigy executable... ');
        $target = $binFile->copyTo(Effigy::$project->rootDir->getPath());
        $target->setPermissions(0777);
        $this->io->{'success'}('done');

        umask($umask);
        $this->io->newLine();

        return Effigy::$project->installDev(...static::Packages);
    }
}
