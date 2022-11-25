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
use DecodeLabs\Effigy\Task\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Integra;

class GenerateEcsConfig implements Task
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return Integra::$rootDir->getFile('ecs.php');
    }

    protected function getTemplate(): Template
    {
        return new EcsTemplate();
    }
}
