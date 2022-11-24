<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy\Task\GeneratePhpstanConfig\PhpstanTemplate;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Integra;

class GeneratePhpstanConfig implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Integra::$rootDir->getFile('phpstan.neon');
    }

    protected function getTemplate(): Template
    {
        return new PhpstanTemplate();
    }
}
