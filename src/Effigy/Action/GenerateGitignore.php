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

class GenerateGitignore implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->effigy->project->rootDir->getFile('.gitignore');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateGitignore/gitignore.template',
            $this->effigy,
            $this->io,
        );
    }
}
