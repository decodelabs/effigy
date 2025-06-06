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
use DecodeLabs\Clip\Controller\Commandment as CommandmentController;
use DecodeLabs\Coercion;
use DecodeLabs\Effigy;
use DecodeLabs\Exceptional;
use DecodeLabs\Integra\Project;
use DecodeLabs\Monarch;
use DecodeLabs\Nuance\Dumpable;
use DecodeLabs\Nuance\Entity\NativeObject as NuanceEntity;
use DecodeLabs\Systemic;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;
use OndraM\CiDetector\CiDetector;
use Throwable;

/**
 * @phpstan-type TConfig array{
 *     'php'?: string,
 *     'entry'?: string,
 *     'params'?: array<string, string>,
 *     'codeDirs'?: array<string>,
 *     'exports'?: array<string>
 * }
 */
class Controller extends CommandmentController implements
    ControllerInterface,
    Dumpable
{
    #[Plugin]
    protected(set) Config $config;

    #[Plugin]
    protected(set) Project $project;

    protected bool $local = false;
    protected bool $ciMode;
    protected ?File $entryFile = null;


    public function __construct(
        ?Project $project = null
    ) {
        parent::__construct();

        $this->project = $project ?? new Project();
        $this->config = new Config($this->project);

        // Local
        $entry = Atlas::file((string)realpath(
            Coercion::asString($_SERVER['PHP_SELF'])
        ));

        $parent = (string)$entry->getParent();

        $this->local =
            $parent === (string)$this->project->rootDir ||
            $parent === (string)$this->project->rootDir->getDir('bin');

        // Integra config
        if (null !== ($bin = $this->config->getPhpBinary())) {
            $this->project->setBinaryPath('php', $bin);
        }
    }


    public function isLocal(): bool
    {
        return $this->local;
    }

    /**
     * @return $this
     */
    public function setCiMode(
        bool $mode
    ): static {
        $this->ciMode = $mode;
        return $this;
    }

    public function isCiMode(): bool
    {
        if (isset($this->ciMode)) {
            return $this->ciMode;
        }

        $detector = new CiDetector();
        return $this->ciMode = $detector->isCiDetected();
    }


    public function run(
        string $name,
        string ...$args
    ): bool {
        // Composer direct
        if ($name === 'composer') {
            return $this->project->run(...$args);
        }


        // Confirmed app Action
        if ($this->hasAppAction($name)) {
            return $this->runAppAction($name, ...$args);
        }


        // Composer script
        if ($this->hasComposerScript($name)) {
            return $this->runComposerScript($name, ...$args);
        }


        // Commands
        if ($this->hasAction($name)) {
            if ($this->runAction($name, array_values($args))) {
                $this->config->save();
                return true;
            } else {
                return false;
            }
        }


        // Bin
        if ($this->hasVendorBin($name)) {
            return Systemic::command([
                    (string)$this->project->rootDir->getFile('vendor/bin/' . $name),
                    ...$args
                ])
                ->setWorkingDirectory(Monarch::$paths->working)
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }


        return $this->runAppAction($name, ...$args);
    }

    public function runGit(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            ['git', $name, ...$args],
            $this->project->rootDir
        );
    }

    public function askGit(
        string $name,
        string ...$args
    ): ?string {
        $result = Systemic::capture(
            ['git', $name, ...$args],
            $this->project->rootDir
        );

        if (!$result->wasSuccessful()) {
            return null;
        }

        return $result->getOutput();
    }



    public function canRun(
        string $name
    ): bool {
        return
            $this->hasComposerScript($name) ||
            $this->hasAction($name) ||
            $this->hasVendorBin($name);
    }


    /**
     * @return array<string>
     */
    public function getComposerScripts(): array
    {
        return $this->project->getScripts();
    }

    public function hasComposerScript(
        string $name
    ): bool {
        return $this->project->hasScript($name);
    }

    public function runComposerScript(
        string $name,
        string ...$args
    ): bool {
        return $this->project->run($name, ...$args);
    }



    /**
     * @return array<File>
     */
    public function getVendorBins(): array
    {
        return $this->project->getBins();
    }

    public function hasVendorBin(
        string $name
    ): bool {
        $ignored = $this->config->getIgnoredBins();

        if (in_array($name, $ignored)) {
            return false;
        }

        return $this->project->hasBin($name);
    }



    public function getEntryFile(): ?File
    {
        if ($this->entryFile !== null) {
            return $this->entryFile;
        }

        // Fallback to generic entry.php
        if (!$this->config->hasEntry()) {
            $file = $this->project->rootDir->getFile('entry.php');

            if ($file->exists()) {
                return $file;
            }

            return null;
        }


        // Parse config
        $entry = (string)$this->config->getEntry();
        $matches = [];

        if (false === preg_match_all('|{{([a-zA-Z0-9\-_]+)}}|', $entry, $matches)) {
            throw Exceptional::UnexpectedValue(
                message: 'Unable to parse entry file config',
                data: $entry
            );
        }

        // @phpstan-ignore-next-line
        $matches = $matches[1] ?? [];

        if (!empty($matches)) {
            $io = $this->getIoSession();

            $io->newLine();
            $io->write('Found entry: ');
            $io->{'..brightYellow'}($entry);

            foreach ($matches as $slug) {
                $param = $this->config->getParam($slug);
                $entry = str_replace('{{' . $slug . '}}', $param, $entry);
            }
        }

        $file = $this->project->rootDir->getFile($entry);

        if ($file->exists()) {
            $this->config->set('entry', $entry);
            return $file;
        }

        throw Exceptional::NotFound(
            message: 'Entry file ' . $entry . ' does not exist'
        );
    }

    public function hasAppAction(
        string $name
    ): bool {
        if ($name === 'action-exists') {
            return true;
        }

        try {
            // Entry file
            if (!$entry = $this->getEntryFile()) {
                return false;
            }

            $this->config->save();

            // Launch script
            $result = Systemic::command([
                    $this->project->getBinaryPath('php'),
                    $entry->path,
                    'action-exists',
                    $name
                ])
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->capture();

            return trim((string)$result->getOutput()) === 'true';
        } catch (Throwable $e) {
            return false;
        }
    }

    public function runAppAction(
        string $name,
        string ...$args
    ): bool {
        // Entry file
        if ($entry = $this->getEntryFile()) {
            $this->config->save();

            // Launch script
            return Systemic::command([
                    $this->project->getBinaryPath('php'),
                    $entry->path,
                    $name,
                    ...$args
                ])
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }

        // Clip
        elseif ($this->hasVendorBin('clip')) {
            return Systemic::command([
                    (string)$this->project->rootDir->getFile('vendor/bin/clip'),
                    $name,
                    ...$args
                ])
                ->setWorkingDirectory(Monarch::$paths->working)
                ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
                ->run();
        }


        throw Exceptional::NotFound(
            message: 'Effigy couldn\'t find any appropriate ways to run "' . $name . '"'
        );
    }




    /**
     * @return array<string, Dir>
     */
    public function getCodeDirs(): array
    {
        return $this->config->getCodeDirs();
    }

    /**
     * @return array<string>
     */
    public function getExportsWhitelist(): array
    {
        return $this->config->getExportsWhitelist();
    }

    /**
     * @return array<string>
     */
    public function getExecutablesWhitelist(): array
    {
        return $this->config->getExecutablesWhitelist();
    }


    public function getGlobalPath(): string
    {
        $result = Systemic::capture(
            ['composer', 'config', 'home', '--global'],
            $this->project->rootDir
        );

        if (!$result->wasSuccessful()) {
            throw Exceptional::Runtime(
                message: 'Unable to locate global composer path'
            );
        }

        $output = trim((string)$result->getOutput());

        if (
            empty($output) ||
            !is_dir($output)
        ) {
            throw Exceptional::Runtime(
                message: 'Invalid global composer path: ' . $output
            );
        }

        return $output;
    }


    public function toNuanceEntity(): NuanceEntity
    {
        $entity = new NuanceEntity($this);
        $entity->setProperty('local', $this->local, 'protected');
        $entity->setProperty('config', $this->config, 'protected');
        $entity->setProperty('entryFile', $this->entryFile, 'protected');
        return $entity;
    }
}

Veneer::register(
    Controller::class,
    Effigy::class
);
