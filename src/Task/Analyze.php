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

        Cli::$command
            ->addArgument('-clear|c', 'Clear cache')
            ->addArgument('-debug|d', 'Debug mode')
            ->addArgument('-config=', 'Composer script name')
            ->addArgument('-configuration=', 'Configuration file name');

        // Clear
        if (Cli::$command['clear']) {
            return Integra::runGlobalBin('phpstan', 'clear-result-cache');
        }

        $confName = Cli::$command['config'];

        // Main analyze
        $args = ['phpstan'];

        if (Cli::$command['debug']) {
            $args[] = '--debug';
        }

        if (Effigy::isCiMode()) {
            $args[] = '--no-progress';
        }

        if ($confName === null) {
            if ($confFile = Cli::$command['configuration']) {
                $args[] = '--configuration=' . $confFile;
            }

            if (!Integra::runGlobalBin(...$args)) {
                return false;
            }

            if ($confFile) {
                return true;
            }
        }



        // Specialised analyze
        $scripts = Effigy::getComposerScripts();

        foreach ($scripts as $name => $script) {
            if (!preg_match('/^analyze\-([a-zA-Z0-9-_]+)$/', $name, $matches)) {
                continue;
            }

            $name = $matches[1];

            if (
                $confName !== null &&
                $name !== $confName
            ) {
                continue;
            }

            $config = '--configuration=phpstan.' . $name . '.neon';

            if (!Integra::runGlobalBin(...[...$args, $config])) {
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
