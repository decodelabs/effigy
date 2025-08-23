<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action\Deploy;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Monarch;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class Upgrade implements Action
{
    public function __construct(
        protected Session $io,
        protected Effigy $effigy,
        protected Systemic $systemic
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $this->io->newLine();

        $this->updateGit();
        $this->updateComposer();
        $this->build();
        return true;
    }

    protected function updateGit(): void
    {
        $this->io->info('Updating git...');

        // Git pull
        $this->systemic->run(
            ['git', 'pull'],
            Monarch::getPaths()->root
        );

        $this->io->newLine();
        $this->io->newLine();
    }

    protected function updateComposer(): void
    {
        $this->io->info('Updating composer...');

        $args = [];
        $args[] = '--no-dev';

        $this->systemic->run(
            ['composer', 'install', ...$args],
            Monarch::getPaths()->root
        );

        $this->io->newLine();
        $this->io->newLine();
    }

    protected function build(): void
    {
        if (!$this->effigy->hasAppAction('deploy/build')) {
            return;
        }

        $this->io->info('Building...');

        $this->effigy->runAppAction('deploy/build', '--from-source');
    }
}
