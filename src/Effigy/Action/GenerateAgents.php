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

    /**
     * TODO: sort out proper vendor dir detection
     */
    protected function getTemplate(): Template
    {
        $root = dirname(__DIR__, 3);

        if (str_contains($root, 'vendor')) {
            $root = explode('/vendor/', $root)[0];
        }

        return new Template(
            $root . '/vendor/decodelabs/chorus/templates/AGENTS.template.md',
            $this->effigy,
            $this->io,
        );
    }
}
