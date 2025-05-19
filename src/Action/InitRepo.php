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
    public function execute(
        Request $request,
    ): bool {
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

        return true;
    }

    protected function hasBranch(
        string $branch
    ): bool {
        $list = Effigy::askGit('branch', '--list', $branch);
        return trim(trim((string)$list, '*')) === $branch;
    }
}
