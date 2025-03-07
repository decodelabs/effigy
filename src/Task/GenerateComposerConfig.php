<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Task;
use DecodeLabs\Clip\Task\GenerateFileTrait;
use DecodeLabs\Effigy\Task\GenerateComposerConfig\ComposerTemplate;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Integra;

class GenerateComposerConfig implements Task
{
    use GenerateFileTrait;

    /**
     * @var array<string>
     */
    protected const array Packages = [
        'decodelabs/exceptional'
    ];

    /**
     * @var array<string>
     */
    protected const array DevPackages = [
        'decodelabs/phpstan-decodelabs'
    ];

    protected function getTargetFile(): File
    {
        return Integra::$rootDir->getFile('composer.json');
    }

    protected function getTemplate(): Template
    {
        return new ComposerTemplate();
    }

    protected function afterFileSave(
        File $file
    ): bool {
        foreach (static::Packages as $package) {
            if (!Integra::install($package)) {
                return false;
            }
        }

        foreach (static::DevPackages as $package) {
            if (!Integra::installDev($package)) {
                return false;
            }
        }

        if (!Integra::uninstallDev('php')) {
            return false;
        }

        Integra::getLocalManifest()->reload();
        return true;
    }
}
