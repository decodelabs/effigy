<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;

class InitRepo implements Task
{
    public function execute(): bool
    {
        // Git init
        if (
            !Effigy::$project->rootDir->getFile('.git/config')->exists() &&
            !Effigy::runGit('init')
        ) {
            return false;
        }


        // Branches
        if (
            !$this->hasBranch('main') &&
            !$this->hasBranch('master')
        ) {
            Effigy::runGit('branch', 'main');
        }

        if (!$this->hasBranch('develop')) {
            Effigy::runGit('branch', 'develop');
        }


        // Checkout develop
        Effigy::runGit('checkout', 'develop');


        // Git flow
        if ($this->hasGitFlow()) {
            Effigy::runGit('flow', 'init');
        }

        return true;
    }

    /**
     * Ask git if branch exists
     */
    protected function hasBranch(
        string $branch
    ): bool {
        $list = Effigy::askGit('branch', '--list', $branch);
        return trim(trim((string)$list, '*')) === $branch;
    }

    protected function hasGitFlow(): bool
    {
        return Effigy::askGit('flow', 'version') !== null;
    }
}
