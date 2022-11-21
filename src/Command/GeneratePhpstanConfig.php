<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas\File;
use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Command\GeneratePhpstanConfig\PhpstanTemplate;
use DecodeLabs\Effigy\Template;

class GeneratePhpstanConfig implements Command
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->controller->rootDir->getFile('phpstan.neon');
    }

    protected function getTemplate(): Template
    {
        return new PhpstanTemplate($this->controller);
    }
}
