<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class Eclint implements Task
{
    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::ComponentUnavailable(
                message: 'eclint is not installed'
            );
        }

        /*
        Cli::$command
            ->addArgument('-check|c', 'Check standards only');
        */

        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        //$command = Cli::$command['check'] ? 'check' : 'fix';
        $command = 'check';
        $paths = array_map(
            fn ($dir) => $dir->getPath(),
            $dirs
        );

        $output = Systemic::run([
            'eclint', $command, ...$paths
        ]);


        if ($output) {
            Cli::success('No linting issues found');
        }

        return $output;
    }

    protected function ensureInstalled(): bool
    {
        if (Systemic::$os->which('eclint') === 'eclint') {
            return false;
        }

        $confFile = Effigy::$project->rootDir->getFile('.editorconfig');

        if (!$confFile->exists()) {
            $template = new Template(
                __DIR__ . '/GenerateEditorConfig/editorconfig.template'
            );

            $template->saveTo($confFile);
        }

        return true;
    }
}
