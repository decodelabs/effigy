<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Action\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

#[Argument\Flag(
    name: 'check',
    shortcut: 'c',
    description: 'Check standards only'
)]
class Format implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        if (!$this->ensureInstalled()) {
            throw Exceptional::Runtime(
                message: 'Unable to find or create an ecs.php config'
            );
        }

        $args = ['ecs'];

        if (!$request->parameters->asBool('check')) {
            $args[] = '--fix';
        }

        if ($this->effigy->ciMode) {
            $args[] = '--no-progress-bar';
        }

        if ($this->effigy->local) {
            return $this->effigy->project->runBin(...$args);
        } else {
            return $this->effigy->project->runGlobalBin(...$args);
        }
    }

    protected function ensureInstalled(): bool
    {
        // ECS file
        $ecsFile = $this->effigy->project->rootDir->getFile('ecs.php');

        if (!$ecsFile->exists()) {
            $template = new EcsTemplate($this->effigy, $this->io);
            $template->saveTo($ecsFile);
        }

        return true;
    }
}
