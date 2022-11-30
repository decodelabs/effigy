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
            ->addArgument('-debug|d', 'Debug mode');

        Cli::prepareArguments();

        // Clear
        if (Cli::getArgument('clear')) {
            return Integra::runGlobalBin('phpstan', 'clear-result-cache');
        }



        // Main analyze
        $args = ['phpstan'];

        if (Cli::getArgument('debug')) {
            $args[] = '--debug';
        }

        if (Effigy::isCiMode()) {
            $args[] = '--no-progress';
        }

        if (!Integra::runGlobalBin(...$args)) {
            return false;
        }



        // Specialised analyze
        $scripts = Effigy::getComposerScripts();

        foreach ($scripts as $name => $script) {
            if (!preg_match('/^analyze\-/', $name)) {
                continue;
            }

            if (!Integra::runScript($name)) {
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
