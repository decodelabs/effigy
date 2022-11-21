<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas\File;
use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Command\GenerateComposerConfig\ComposerTemplate;
use DecodeLabs\Effigy\Template;

class GenerateComposerConfig implements Command
{
    use GenerateFileTrait;

    public const PACKAGES = [
        'decodelabs/exceptional'
    ];

    public const DEV_PACKAGES = [
        'decodelabs/glitch'
    ];

    protected function getTargetFile(): File
    {
        return $this->controller->rootDir->getFile('composer.json');
    }

    protected function getTemplate(): Template
    {
        return new ComposerTemplate($this->controller);
    }

    protected function afterFileSave(File $file): bool
    {
        foreach (static::PACKAGES as $package) {
            if (!$this->controller->run('composer', 'require', $package)) {
                return false;
            }
        }

        foreach (static::DEV_PACKAGES as $package) {
            if (!$this->controller->run('composer', 'require', $package, '--dev')) {
                return false;
            }
        }

        if (!$this->controller->run('composer', 'remove', 'php', '--dev')) {
            return false;
        }

        return true;
    }
}
