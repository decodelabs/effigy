<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Action\GeneratePhpstanConfig\PhpstanTemplate;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

#[Argument\Flag(
    name: 'clear',
    shortcut: 'c',
    description: 'Clear cache'
)]
#[Argument\Flag(
    name: 'debug',
    shortcut: 'd',
    description: 'Debug mode'
)]
#[Argument\Option(
    name: 'configuration',
    description: 'Configuration file name'
)]
class Analyze implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        if (!$this->ensureInstalled()) {
            throw Exceptional::Runtime(
                message: 'Unable to find or create a PHPStan neon config'
            );
        }

        // Clear
        $clear = $request->parameters->asBool('clear');

        if ($clear) {
            return $this->effigy->project->runBin('phpstan', 'clear-result-cache');
        }


        // Main analyze
        $args = ['phpstan'];

        if ($request->parameters->asBool('debug')) {
            $args[] = '--debug';
        }

        if ($this->effigy->ciMode) {
            $args[] = '--no-progress';
        }

        if ($confFile = $request->parameters->tryString('configuration')) {
            $confs = [$confFile];
        } else {
            $confs = $this->findConfigFiles();
        }

        foreach ($confs as $conf) {
            $runArgs = $args;
            $runArgs[] = '--configuration=' . $conf;

            if (!$this->effigy->project->runBin(...$runArgs)) {
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

        $currentPackage = $this->effigy->project->getLocalManifest()->getName();

        foreach ($packages as $i => $package) {
            if (
                $package === $currentPackage ||
                $this->effigy->project->hasPackage($package)
            ) {
                unset($packages[$i]);
            }
        }

        if (!empty($packages)) {
            $this->effigy->project->installDev(...$packages);
        }

        // Neon file
        $neonFile = $this->effigy->project->rootDir->getFile('phpstan.neon');

        if (
            !$neonFile->exists() &&
            empty($this->findConfigFiles())
        ) {
            $template = new PhpstanTemplate($this->effigy, $this->io);
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

        foreach ($this->effigy->project->rootDir->scanFiles(function (string $name) {
            return preg_match('/phpstan\.([a-zA-Z0-9-_]+\.)?neon$/', $name);
        }) as $file) {
            $output[] = $file->name;
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
