<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class InitRepo implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        // Git init
        if (!$this->controller->rootDir->getFile('.git/config')->exists()) {
            $result = Systemic::$process->launch(
                'git init',
                [],
                $this->controller->rootDir,
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
                $this->controller->rootDir,
                Cli::getSession()
            );
        }

        if (!$this->hasBranch('develop')) {
            Systemic::$process->launch(
                'git branch develop',
                [],
                $this->controller->rootDir,
                Cli::getSession()
            );
        }

        Systemic::$process->launch(
            'git checkout develop',
            [],
            $this->controller->rootDir,
            Cli::getSession()
        );


        // Git flow
        if ($this->hasGitFlow()) {
            Systemic::$process->launch(
                'git flow init',
                [],
                $this->controller->rootDir,
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
            $this->controller->rootDir
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
            $this->controller->rootDir
        )
            ->setDecoratable(false)
            ->launch();

        return $result->wasSuccessful();
    }
}
