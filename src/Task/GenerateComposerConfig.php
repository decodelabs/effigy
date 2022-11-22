<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Task\GenerateComposerConfig\ComposerTemplate;
use DecodeLabs\Effigy\Template;

class GenerateComposerConfig implements Task
{
    use GenerateFileTrait;

    public const PACKAGES = [
        'decodelabs/exceptional'
    ];

    public const DEV_PACKAGES = [
        'decodelabs/glitch',
        'decodelabs/phpstan-decodelabs'
    ];

    protected function getTargetFile(): File
    {
        return Effigy::$rootDir->getFile('composer.json');
    }

    protected function getTemplate(): Template
    {
        return new ComposerTemplate();
    }

    protected function afterFileSave(File $file): bool
    {
        foreach (static::PACKAGES as $package) {
            if (!Effigy::run('composer', 'require', $package)) {
                return false;
            }
        }

        foreach (static::DEV_PACKAGES as $package) {
            if (!Effigy::run('composer', 'require', $package, '--dev')) {
                return false;
            }
        }

        if (!Effigy::run('composer', 'remove', 'php', '--dev')) {
            return false;
        }

        Effigy::reloadComposerConfig();
        return true;
    }
}
