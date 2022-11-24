<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Integra;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class InitRepo implements Task
{
    public function execute(): bool
    {
        // Git init
        if (!Integra::$rootDir->getFile('.git/config')->exists()) {
            $result = Systemic::$process->launch(
                'git init',
                [],
                Integra::$rootDir,
                Cli::getSession()
            );

            if (!$result->wasSuccessful()) {
                return false;
            }
        }

        // Branches
        if (
            !$this->hasBranch('main') &&
            !$this->hasBranch('master')
        ) {
            Systemic::$process->launch(
                'git branch main',
                [],
                Integra::$rootDir,
                Cli::getSession()
            );
        }

        if (!$this->hasBranch('develop')) {
            Systemic::$process->launch(
                'git branch develop',
                [],
                Integra::$rootDir,
                Cli::getSession()
            );
        }

        Systemic::$process->launch(
            'git checkout develop',
            [],
            Integra::$rootDir,
            Cli::getSession()
        );


        // Git flow
        if ($this->hasGitFlow()) {
            Systemic::$process->launch(
                'git flow init',
                [],
                Integra::$rootDir,
                Cli::getSession()
            );
        }

        return true;
    }

    /**
     * Ask git if branch exists
     */
    protected function hasBranch(string $branch): bool
    {
        $result = Systemic::$process->newLauncher(
            'git branch --list ' . $branch,
            [],
            Integra::$rootDir
        )
            ->setDecoratable(false)
            ->launch();

        return trim(trim((string)$result->getOutput(), '*')) === $branch;
    }

    protected function hasGitFlow(): bool
    {
        $result = Systemic::$process->newLauncher(
            'git flow version',
            [],
            Integra::$rootDir
        )
            ->setDecoratable(false)
            ->launch();

        return $result->wasSuccessful();
    }
}
