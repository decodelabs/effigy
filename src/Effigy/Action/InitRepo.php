<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;

class InitRepo implements Action
{
    public function __construct(
        protected Effigy $effigy,
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        // Git init
        if (
            !$this->effigy->project->rootDir->getFile('.git/config')->exists() &&
            !$this->effigy->runGit('init')
        ) {
            return false;
        }


        // Branches
        if (
            !$this->hasBranch('main') &&
            !$this->hasBranch('master')
        ) {
            $this->effigy->runGit('branch', 'main');
        }

        if (!$this->hasBranch('develop')) {
            $this->effigy->runGit('branch', 'develop');
        }


        // Checkout develop
        $this->effigy->runGit('checkout', 'develop');

        return true;
    }

    protected function hasBranch(
        string $branch
    ): bool {
        $list = $this->effigy->askGit('branch', '--list', $branch);
        return trim(trim((string)$list, '*')) === $branch;
    }
}
