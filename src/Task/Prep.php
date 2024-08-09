<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus as Cli;

class Prep implements Task
{
    public function execute(): bool
    {
        // Unmount
        Cli::newLine();
        Cli::info('Unmounting dev packages');

        if (!Effigy::run('unmount')) {
            return false;
        }

        Cli::newLine();

        // Update
        if (!Effigy::run('upgrade')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();


        // Analysis
        Cli::info('Clear PHPStan cache');

        if (!Effigy::run('analyze', '--clear')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();

        Cli::info('Run PHPStan analysis');

        if (!Effigy::run('analyze')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Standards
        Cli::info('Ensure proper code formatting');

        if (!Effigy::run('format')) {
            return false;
        }

        Cli::newLine();
        Cli::newLine();

        // Lint
        Cli::info('Ensure all files are syntax compliant');

        if (!Effigy::run('lint')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // EC Lint
        Cli::info('Check for editorconfig issues');

        if (!Effigy::run('eclint')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Permissions
        Cli::info('Check file permissions');

        if (!Effigy::run('check-executable-permissions')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Exports
        Cli::info('Check package exports');

        if (!Effigy::run('check-git-exports')) {
            return false;
        }


        Cli::newLine();
        Cli::newLine();

        // Non ascii
        Cli::info('Check for non-ASCII characters');

        if (!Effigy::run('check-non-ascii')) {
            return false;
        }


        return true;
    }
}
