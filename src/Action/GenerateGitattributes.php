<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Action\GenerateFileTrait;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;

class GenerateGitattributes implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Effigy::$project->rootDir->getFile('.gitattributes');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateGitattributes/gitattributes.template'
        );
    }
}
