<?php

/**
 * Effigy
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Atlas\File;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Effigy\Template;

class GenerateAgents implements Action
{
    use GenerateFileTrait;

    protected function getTargetFile(): File
    {
        return $this->effigy->project->rootDir->getFile('AGENTS.md');
    }

    protected function getTemplate(): Template
    {
        return new Template(
            dirname(__DIR__, 3) . '/vendor/decodelabs/chorus/templates/AGENTS.template.md',
            $this->effigy,
            $this->io,
        );
    }
}
