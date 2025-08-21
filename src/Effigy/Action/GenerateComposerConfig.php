<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Effigy\Action\GenerateComposerConfig\ComposerTemplate;
use DecodeLabs\Effigy\Template;

class GenerateComposerConfig implements Action
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
        return $this->effigy->project->rootDir->getFile('composer.json');
    }

    protected function getTemplate(): Template
    {
        return new ComposerTemplate(
            $this->effigy,
            $this->io
        );
    }

    protected function afterFileSave(
        File $file
    ): bool {
        foreach (static::Packages as $package) {
            if (!$this->effigy->project->install($package)) {
                return false;
            }
        }

        foreach (static::DevPackages as $package) {
            if (!$this->effigy->project->installDev($package)) {
                return false;
            }
        }

        if (!$this->effigy->project->uninstallDev('php')) {
            return false;
        }

        $this->effigy->project->getLocalManifest()->reload();
        return true;
    }
}
