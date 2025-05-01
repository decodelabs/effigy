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
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;

class GenerateGithubWorkflow implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Effigy::$project->rootDir->getFile('.github/workflows/integrate.yml');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            __DIR__ . '/GenerateGithubWorkflow/integrate.template'
        );
    }
}
