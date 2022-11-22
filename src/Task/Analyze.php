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
        $config = Effigy::getComposerConfig();

        if (
            /** @phpstan-ignore-next-line */
            !isset($config['require-dev']['decodelabs/phpstan-decodelabs']) &&
            /** @phpstan-ignore-next-line */
            !isset($config['require']['decodelabs/phpstan-decodelabs'])
        ) {
            Effigy::run('composer', 'require', 'decodelabs/phpstan-decodelabs', '--dev');
        }

        // Neon file
        $neonFile = Effigy::$rootDir->getFile('phpstan.neon');

        if (!$neonFile->exists()) {
            $template = new PhpstanTemplate();
            $template->saveTo($neonFile);
        }

        return true;
    }
}
