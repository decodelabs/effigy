<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas\File;
use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Template;

class GenerateEditorConfig implements Command
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->controller->rootDir->getFile('.editorconfig');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            $this->controller,
            __DIR__ . '/GenerateEditorConfig/editorconfig.template'
        );
    }
}
