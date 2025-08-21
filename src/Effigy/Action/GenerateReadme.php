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

class GenerateReadme implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->effigy->project->rootDir->getFile('README.md');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateReadme/readme.template',
            $this->effigy,
            $this->io,
        );
    }
}
