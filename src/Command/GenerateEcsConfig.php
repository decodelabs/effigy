<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas\File;
use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Command\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Effigy\Template;

class GenerateEcsConfig implements Command
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->controller->rootDir->getFile('ecs.php');
    }

    protected function getTemplate(): Template
    {
        return new EcsTemplate($this->controller);
    }
}
