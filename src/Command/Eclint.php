<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;

class Eclint implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::ComponentUnavailable('eclint is not installed');
        }

        /*
        Cli::getCommandDefinition()
            ->addArgument('-check|c', 'Check standards only');

        Cli::prepareArguments();
        */

        $dirs = $this->controller->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        //$command = Cli::getArgument('check') ? 'check' : 'fix';
        $command = 'check';
        $paths = array_keys($dirs);

        $result = Systemic::$process->newLauncher('eclint', [
                $command, ...$paths
            ])
            ->setSession(Cli::getSession())
            ->launch();

        $output = $result->wasSuccessful();

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

        $confFile = $this->controller->rootDir->getFile('.editorconfig');

        if (!$confFile->exists()) {
            $template = new Template(
                $this->controller,
                __DIR__ . '/GenerateEditorConfig/editorconfig.template'
            );

            $template->saveTo($confFile);
        }

        return true;
    }
}
