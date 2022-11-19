<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
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
            $content = <<<CONF
# https://EditorConfig.org

root = true

[*]
indent_style = space
indent_size = 4
end_of_line = lf
charset = utf-8
trim_trailing_whitespace = true
insert_final_newline = true
block_comment_start = /*
block_comment = *
block_comment_end = */

[*.yml]
indent_size = 2

[*.md]
trim_trailing_whitespace = false
CONF;

            $confFile->putContents($content);
        }

        return true;
    }
}
