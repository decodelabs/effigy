<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
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
            throw Exceptional::Runtime(
                message: 'Unable to find or create a PHPStan neon config'
            );
        }

        Cli::$command
            ->addArgument('-clear|c', 'Clear cache')
            ->addArgument('-debug|d', 'Debug mode')
            ->addArgument('-configuration=', 'Configuration file name');

        // Clear
        if (Cli::$command['clear']) {
            return Integra::runBin('phpstan', 'clear-result-cache');
        }


        // Main analyze
        $args = ['phpstan'];

        if (Cli::$command['debug']) {
            $args[] = '--debug';
        }

        if (Effigy::isCiMode()) {
            $args[] = '--no-progress';
        }

        if ($confFile = Coercion::tryString(
            Cli::$command['configuration']
        )) {
            $confs = [$confFile];
        } else {
            $confs = $this->findConfigFiles();
        }

        foreach ($confs as $conf) {
            $runArgs = $args;
            $runArgs[] = '--configuration=' . $conf;

            if (!Integra::runBin(...$runArgs)) {
                return false;
            }
        }

        return true;
    }

    protected function ensureInstalled(): bool
    {
        $packages = [
            'decodelabs/phpstan-decodelabs'
        ];

        $currentPackage = Integra::getLocalManifest()->getName();

        foreach ($packages as $i => $package) {
            if (
                $package === $currentPackage ||
                Integra::hasPackage($package)
            ) {
                unset($packages[$i]);
            }
        }

        if (!empty($packages)) {
            Integra::installDev(...$packages);
        }

        // Neon file
        $neonFile = Integra::$rootDir->getFile('phpstan.neon');

        if (
            !$neonFile->exists() &&
            empty($this->findConfigFiles())
        ) {
            $template = new PhpstanTemplate();
            $template->saveTo($neonFile);
        }

        return true;
    }

    /**
     * Find all PHPStan config files
     *
     * @return array<string>
     */
    protected function findConfigFiles(): array
    {
        $output = [];

        foreach (Integra::$rootDir->scanFiles(function (string $name) {
            return preg_match('/phpstan\.([a-zA-Z0-9-_]+\.)?neon$/', $name);
        }) as $file) {
            $output[] = $file->getName();
        }

        usort($output, function ($a, $b) {
            if ($a == 'phpstan.neon') {
                return -1;
            } elseif ($b == 'phpstan.neon') {
                return 1;
            }

            return strcmp($a, $b);
        });

        return $output;
    }
}
