<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Task\GeneratePhpstanConfig\PhpstanTemplate;
use DecodeLabs\Exceptional;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class Analyze implements Task
{
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
            return Effigy::run('composer', 'global', 'exec', 'phpstan', 'clear-result-cache');
        }


        // Main analyze
        $args = ['composer', 'global', 'exec', 'phpstan'];
        $composerArgs = ['--'];

        if (Cli::getArgument('headless')) {
            $args[] = '--no-interaction';
        }


        if (Cli::getArgument('debug')) {
            $composerArgs[] = '--debug';
        }

        if (Cli::getArgument('headless')) {
            $composerArgs[] = '--no-progress';
        }

        if (!Effigy::run(...$args, ...$composerArgs)) {
            return false;
        }



        // Specialised analyze
        $scripts = Effigy::getComposerScripts();

        foreach ($scripts as $script) {
            if (!preg_match('/^analyze\-/', $script)) {
                continue;
            }

            if (!Effigy::run('composer', 'run-script', $script)) {
                return false;
            }
        }

        return true;
    }

    protected function ensureInstalled(): bool
    {
        // ext dir
        if (!Integra::hasPackage('decodelabs/phpstan-decodelabs')) {
            Integra::installDev('decodelabs/phpstan-decodelabs');
        }

        // Neon file
        $neonFile = Integra::$rootDir->getFile('phpstan.neon');

        if (!$neonFile->exists()) {
            $template = new PhpstanTemplate();
            $template->saveTo($neonFile);
        }

        return true;
    }
}
