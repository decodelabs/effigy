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

class GenerateReadme implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Effigy::$rootDir->getFile('README.md');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateReadme/readme.template'
        );
    }
}
