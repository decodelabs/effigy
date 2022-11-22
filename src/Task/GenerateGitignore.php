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
use DecodeLabs\Effigy\Template;

class GenerateGitignore implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Effigy::$rootDir->getFile('.gitignore');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateGitignore/gitignore.template'
        );
    }
}
