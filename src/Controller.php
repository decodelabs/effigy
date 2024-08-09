<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Clip\Controller as ControllerInterface;
use DecodeLabs\Clip\Controller\Generic as GenericController;
use DecodeLabs\Effigy;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Integra;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use OndraM\CiDetector\CiDetector;

/**
 * @phpstan-type TConfig array{
 *     'php'?: string,
 *     'entry'?: string,
 *     'params'?: array<string, string>,
 *     'codeDirs'?: array<string>,
 *     'exports'?: array<string>
 * }
 */
class Controller extends GenericController implements
    ControllerInterface,
    Dumpable
{
    public const USER_FILENAME = 'effigy.json';

    #[Plugin]
    public Config $config;

    protected bool $local = false;
    protected bool $ciMode;
    protected ?File $entryFile = null;


    /**
     * Initialize paths
     */
    public function __construct()
    {
        $this->config = new Config(
            Integra::$rootDir->getFile(self::USER_FILENAME)
        );

        // Local
        $entry = Atlas::file((string)realpath($_SERVER['PHP_SELF']));
        $parent = (string)$entry->getParent();

        $this->local =
            $parent === (string)Integra::$rootDir ||
            $parent === (string)Integra::$rootDir->getDir('bin');

        if ($this->local) {
            Integra::forceLocal(true);
        }

        // Integra config
        Integra::setPhpBinary($this->config->getPhpBinary());
        Integra::setCiMode($this->isCiMode());
    }


    /**
     * Is local installation
     */
    public function isLocal(): bool
    {
        return $this->local;
    }

    /**
     * Set CI mode
     *
     * @return $this
     */
    public function setCiMode(
        bool $mode
    ): static {
        $this->ciMode = $mode;
        return $this;
    }

    /**
     * Get CI mode
     */
    public function isCiMode(): bool
    {
        if (isset($this->ciMode)) {
            return $this->ciMode;
        }

        $detector = new CiDetector();
        return $this->ciMode = $detector->isCiDetected();
    }


    /**
     * Run controller
     */
    public function run(
        string $name,
        string ...$args
    ): bool {
        // Composer direct
        if ($name === 'composer') {
            return Integra::run(...$args);
        }


        // Composer script
        if ($this->hasComposerScript($name)) {
            return $this->runComposerScript($name, ...$args);
        }


        // Commands
        if ($this->hasTask($name)) {
            if ($this->runTask($name, $args)) {
                $this->config->save();
                return true;
            } else {
                return false;
            }
        }


        // Bin
        if ($this->hasVendorBin($name)) {
            return Systemic::command([
                    (string)Integra::$rootDir->getFile('vendor/bin/' . $name),
                    ...$args
                ])
                ->setWorkingDirectory(Integra::$runDir)
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }


        // Entry file
        if ($entry = $this->getEntryFile()) {
            $this->config->save();

            // Launch script
            return Systemic::command([
                    Integra::getPhpBinary(),
                    $entry->getPath(),
                    $name,
                    ...$args
                ])
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }

        // Clip
        elseif ($this->hasVendorBin('clip')) {
            return Systemic::command([
                    (string)Integra::$rootDir->getFile('vendor/bin/clip'),
                    $name,
                    ...$args
                ])
                ->setWorkingDirectory(Integra::$runDir)
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }


        throw Exceptional::NotFound(
            'Effigy couldn\'t find any appropriate ways to run "' . $name . '"'
        );
    }

    /**
     * Run git command
     */
    public function runGit(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            ['git', $name, ...$args],
            Integra::$rootDir
        );
    }

    /**
     * Ask git quietly
     */
    public function askGit(
        string $name,
        string ...$args
    ): ?string {
        $result = Systemic::capture(
            ['git', $name, ...$args],
            Integra::$rootDir
        );

        if (!$result->wasSuccessful()) {
            return null;
        }

        return $result->getOutput();
    }



    /**
     * Can run script or command
     */
    public function canRun(
        string $name
    ): bool {
        return
            $this->hasComposerScript($name) ||
            $this->hasTask($name) ||
            $this->hasVendorBin($name);
    }


    /**
     * Get list of composer scripts
     *
     * @return array<string>
     */
    public function getComposerScripts(): array
    {
        return Integra::getScripts();
    }

    /**
     * Composer script exists
     */
    public function hasComposerScript(
        string $name
    ): bool {
        return Integra::hasScript($name);
    }

    /**
     * Run composer script
     */
    public function runComposerScript(
        string $name,
        string ...$args
    ): bool {
        return Integra::run($name, ...$args);
    }



    /**
     * Get vendor bins
     *
     * @return array<File>
     */
    public function getVendorBins(): array
    {
        return Integra::getBins();
    }

    /**
     * Composer vendor bin exists
     */
    public function hasVendorBin(
        string $name
    ): bool {
        $ignored = $this->config->getIgnoredBins();

        if (in_array($name, $ignored)) {
            return false;
        }

        return Integra::hasBin($name);
    }



    /**
     * Get entry file
     */
    public function getEntryFile(): ?File
    {
        if ($this->entryFile !== null) {
            return $this->entryFile;
        }

        // Fallback to generic entry.php
        if (!$this->config->hasEntry()) {
            $file = Integra::$rootDir->getFile('entry.php');

            if ($file->exists()) {
                return $file;
            }

            return null;
        }


        // Parse config
        $entry = (string)$this->config->getEntry();
        $matches = [];

        if (false === preg_match_all('|{{([a-zA-Z0-9\-_]+)}}|', $entry, $matches)) {
            throw Exceptional::UnexpectedValue('Unable to parse entry file config', null, $entry);
        }

        $matches = $matches[1] ?? [];

        if (!empty($matches)) {
            Cli::newLine();
            Cli::write('Found entry: ');
            Cli::{'..brightYellow'}($entry);

            foreach ($matches as $slug) {
                $param = $this->config->getParam($slug);
                $entry = str_replace('{{' . $slug . '}}', $param, $entry);
            }
        }

        $file = Integra::$rootDir->getFile($entry);

        if ($file->exists()) {
            $this->config->set('entry', $entry);
            return $file;
        }

        throw Exceptional::NotFound('Entry file ' . $entry . ' does not exist');
    }




    /**
     * Get code paths
     *
     * @return array<string, Dir>
     */
    public function getCodeDirs(): array
    {
        return $this->config->getCodeDirs();
    }

    /**
     * Get exports whitelist
     *
     * @return array<string>
     */
    public function getExportsWhitelist(): array
    {
        return $this->config->getExportsWhitelist();
    }



    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'properties' => [
            '*local' => $this->local,
            '*config' => $this->config,
            '*entryFile' => $this->entryFile
        ];
    }
}

/** @phpstan-ignore-next-line */
Veneer::register(Controller::class, Effigy::class);
