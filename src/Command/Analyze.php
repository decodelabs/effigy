<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Terminus as Cli;

class Analyze implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): void
    {
        $this->ensureInstalled();

        Cli::getCommandDefinition()
            ->addArgument('-clear|c', 'Clear cache')
            ->addArgument('-debug|d', 'Debug mode');

        Cli::prepareArguments();

        // Clear
        if (Cli::getArgument('clear')) {
            $this->controller->run('composer', 'exec', 'phpstan', 'clear-result-cache');
            return;
        }


        // Main analyze
        $args = ['composer', 'exec', 'phpstan'];

        if (Cli::getArgument('debug')) {
            $args[] = '-- --debug';
        }

        $this->controller->run(...$args);



        // Specialised analyze
        $scripts = $this->controller->getComposerScripts();

        foreach ($scripts as $script) {
            if (!preg_match('/^analyze\-/', $script)) {
                continue;
            }

            $this->controller->run('composer', 'run-script', $script);
        }
    }

    protected function ensureInstalled(): void
    {
        // Dependencies
        $execFile = $this->controller->rootDir->getFile('vendor/bin/phpstan');

        if (!$execFile->exists()) {
            $this->controller->run('composer', 'require', 'phpstan/phpstan', '--dev');
            $this->controller->run('composer', 'require', 'phpstan/extension-installer', '--dev');
            $this->controller->run('composer', 'require', 'decodelabs/phpstan-decodelabs', '--dev');
        }

        // Neon file
        $neonFile = $this->controller->rootDir->getFile('phpstan.neon');

        if (!$neonFile->exists()) {
            // TODO: create config
        }
    }
}
