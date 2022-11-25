<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Task;
use DecodeLabs\Clip\Task\GenerateFileTrait;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Integra;

class GenerateReadme implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Integra::$rootDir->getFile('README.md');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateReadme/readme.template'
        );
    }
}
