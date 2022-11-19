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
use DecodeLabs\Terminus as Cli;

class Analyze implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::Runtime('Unable to find or create a PHPStan neon config');
        }

        Cli::getCommandDefinition()
            ->addArgument('-clear|c', 'Clear cache')
            ->addArgument('-debug|d', 'Debug mode')
            ->addArgument('-headless|h', 'No interaction mode');

        Cli::prepareArguments();

        // Clear
        if (Cli::getArgument('clear')) {
            return $this->controller->run('composer', 'exec', 'phpstan', 'clear-result-cache');
        }


        // Main analyze
        $args = ['composer', 'exec', 'phpstan'];

        if (Cli::getArgument('headless')) {
            $args[] = '--no-interaction';
        }

        if (Cli::getArgument('debug')) {
            $args[] = '-- --debug';
        }

        if (!$this->controller->run(...$args)) {
            return false;
        }



        // Specialised analyze
        $scripts = $this->controller->getComposerScripts();

        foreach ($scripts as $script) {
            if (!preg_match('/^analyze\-/', $script)) {
                continue;
            }

            if (!$this->controller->run('composer', 'run-script', $script)) {
                return false;
            }
        }

        return true;
    }

    protected function ensureInstalled(): bool
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
            $dirs = $this->controller->getCodeDirs();

            if (empty($dirs)) {
                return false;
            }

            $paths = [];

            foreach ($dirs as $name => $dir) {
                $paths[] = '- ' . $name;
            }

            $pathString = implode("\n        ", $paths);

            $content = <<<NEON
parameters:
    paths:
        $pathString
    level: max
NEON;

            $neonFile->putContents($content);
        }

        return true;
    }
}