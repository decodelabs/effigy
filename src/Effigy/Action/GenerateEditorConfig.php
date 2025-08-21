<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Effigy\Template;

class GenerateEditorConfig implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->effigy->project->rootDir->getFile('.editorconfig');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateEditorConfig/editorconfig.template',
            $this->effigy,
            $this->io,
        );
    }
}
