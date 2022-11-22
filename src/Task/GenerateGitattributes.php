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

class GenerateGitattributes implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Effigy::$rootDir->getFile('.gitattributes');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateGitattributes/gitattributes.template'
        );
    }
}
