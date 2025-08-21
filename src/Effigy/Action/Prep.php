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
use DecodeLabs\Terminus\Session;

use const PHP_VERSION;

class Prep implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        // Unmount
        $this->io->newLine();
        $this->io->info('Unmounting dev packages');

        if (!$this->effigy->run('unmount')) {
            return false;
        }

        $this->io->newLine();

        // Update
        if (!$this->effigy->run('upgrade')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();


        // Analysis
        /*
        $this->io->info('Clear PHPStan cache');

        if (!$this->effigy->run('analyze', '--clear')) {
            return false;
        }

        $this->io->newLine();
        $this->io->newLine();
        */

        $this->io->info('Run PHPStan analysis');

        if (!$this->effigy->run('analyze')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();

        // Standards
        $this->io->info('Ensure proper code formatting');

        if (version_compare(PHP_VERSION, '8.5.0', '>=')) {
            $this->io->warning('Skipping formatting until PHP-CS-Fixer is updated for PHP 8.5');
        } else {
            if (!$this->effigy->run('format')) {
                return false;
            }
        }

        $this->io->newLine();
        $this->io->newLine();

        // Lint
        $this->io->info('Ensure all files are syntax compliant');

        if (!$this->effigy->run('lint')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();

        // EC Lint
        $this->io->info('Check for editorconfig issues');

        if (!$this->effigy->run('eclint')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();

        // Permissions
        $this->io->info('Check file permissions');

        if (!$this->effigy->run('check-executable-permissions')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();

        // Exports
        $this->io->info('Check package exports');

        if (!$this->effigy->run('check-git-exports')) {
            return false;
        }


        $this->io->newLine();
        $this->io->newLine();

        // Non ascii
        $this->io->info('Check for non-ASCII characters');

        if (!$this->effigy->run('check-non-ascii')) {
            return false;
        }


        return true;
    }
}
