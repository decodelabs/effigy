<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Effigy\Action\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Effigy\Template;

class GenerateEcsConfig implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->effigy->project->rootDir->getFile('ecs.php');
    }

    protected function getTemplate(): Template
    {
        return new EcsTemplate(
            $this->effigy,
            $this->io
        );
    }
}
