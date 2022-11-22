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
use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Systemic;
use DecodeLabs\Systemic\Process\Launcher;
use DecodeLabs\Terminus as Cli;
use DecodeLabs\Veneer\Plugin;

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
    public Dir $runDir;

    #[Plugin]
    public Dir $rootDir;

    #[Plugin]
    public File $composerFile;

    #[Plugin]
    public File $userFile;

    protected bool $local = false;
    protected ?File $entryFile = null;

    /**
     * @phpstan-var TConfig
     */
    protected array $config;

    /**
     * @phpstan-var TConfig
     */
    protected array $newConfig = [];

    /**
     * @var array<string>
     */
    protected array $scripts = [];

    /**
     * @var array<string, mixed>
     */
    protected array $composerConfig;

    /**
     * Initialize paths
     */
    public function __construct(
        Dir $rootDir,
        Dir $runDir,
        File $composerFile
    ) {
        $this->rootDir = $rootDir;
        $this->runDir = $runDir;
        $this->composerFile = $composerFile;

        $this->userFile = $this->loadUserFile();
        $this->config = $this->loadConfig();

        $entry = Atlas::file((string)realpath($_SERVER['PHP_SELF']));

        $parent = (string)$entry->getParent();
        $this->local =
            $parent === (string)$this->rootDir ||
            $parent === (string)$this->rootDir->getDir('bin');
    }


    /**
     * Load user config file
     */
    protected function loadUserFile(): File
    {
        return $this->rootDir->getFile(self::USER_FILENAME);
    }



    /**
     * Parse composer scripts
     *
     * @param array<string, mixed> $scripts
     * @return array<string>
     */
    protected function parseComposerScripts(array $scripts): array
    {
        $output = [];

        foreach ($scripts as $name => $def) {
            $output[] = $name;
        }

        return $output;
    }


    /**
     * Is local installation
     */
    public function isLocal(): bool
    {
        return $this->local;
    }


    /**
     * Run controller
     */
    public function run(
        string $arg,
        string ...$args
    ): bool {
        // Composer script
        if (in_array($arg, $this->scripts)) {
            return $this->newComposerLauncher($args)
                ->launch()
                ->wasSuccessful();
        }


        // Commands
        if ($this->taskExists($arg)) {
            if ($this->runTask($arg, $args)) {
                $this->saveConfig();
                return true;
            } else {
                return false;
            }
        }


        // Bin
        if (
            false === strpos($arg, '/') &&
            $this->rootDir->getFile('vendor/bin/' . $arg)->exists()
        ) {
            return Systemic::$process->launch(
                'vendor/bin/' . $arg,
                $args,
                $this->rootDir,
                Cli::getSession()
            )
                ->wasSuccessful();
        }

        // Entry file
        if (!$entry = $this->getEntryFile()) {
            throw Exceptional::NotFound(
                'Effigy couldn\'t find any appropriate ways to run "' . $arg . '"'
            );
        }

        $this->saveConfig();

        // Launch script
        return $this->newScriptLauncher($entry->getPath(), $args)
            ->launch()
            ->wasSuccessful();
    }

    /**
     * New script launcher
     *
     * @param string|array<string>|null $args
     */
    public function newScriptLauncher(
        string $path,
        string|array|null $args = null
    ): Launcher {
        if ($args === null) {
            $args = [];
        } elseif (!is_array($args)) {
            $args = (array)$args;
        }

        array_unshift($args, $path);
        $user = Systemic::$process->getCurrent()->getOwnerName();

        return Systemic::$process->newLauncher($this->getPhpBinary(), $args, null, null, $user)
            ->setSession(Cli::getSession());
    }

    /**
     * New composer launcher
     *
     * @param string|array<string>|null $args
     */
    public function newComposerLauncher(
        string|array|null $args = null
    ): Launcher {
        if ($args === null) {
            $args = [];
        } elseif (!is_array($args)) {
            $args = (array)$args;
        }

        if (null === ($composer = Systemic::$os->which('composer'))) {
            throw Exceptional::NotFound('Unable to locate global composer executable');
        }

        array_unshift($args, $composer);
        $user = Systemic::$process->getCurrent()->getOwnerName();

        return Systemic::$process->newLauncher($this->getPhpBinary(), $args, null, null, $user)
            ->setSession(Cli::getSession());
    }



    /**
     * Can run script or command
     */
    public function canRun(string $name): bool
    {
        return
            $this->hasComposerScript($name) ||
            $this->taskExists($name);
    }


    /**
     * Get list of composer scripts
     *
     * @return array<string>
     */
    public function getComposerScripts(): array
    {
        return $this->scripts;
    }

    /**
     * Composer script exists
     */
    public function hasComposerScript(string $name): bool
    {
        return in_array($name, $this->scripts);
    }



    /**
     * Get PHP binary
     */
    public function getPhpBinary(): string
    {
        return $this->config['php'] ?? Systemic::$os->which('php') ?? 'php';
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
        if (!isset($this->config['entry'])) {
            $file = $this->rootDir->getFile('entry.php');

            if ($file->exists()) {
                return $file;
            }

            return null;
        }


        // Parse config
        $entry = $this->config['entry'];
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
                $param = $this->getParam($slug);
                $entry = str_replace('{{' . $slug . '}}', $param, $entry);
            }
        }

        $file = $this->rootDir->getFile($entry);

        if ($file->exists()) {
            $this->newConfig['entry'] = $entry;
            return $file;
        }

        throw Exceptional::NotFound('Entry file ' . $entry . ' does not exist');
    }

    /**
     * Get param from config or user
     */
    protected function getParam(string $slug): string
    {
        if (isset($this->config['params'][$slug])) {
            return $this->config['params'][$slug];
        }

        $value = (string)Cli::ask('What is your "' . $slug . '" value?', null, function ($value) {
            return strlen($value) > 0;
        });

        $this->newConfig['params'][$slug] = $value;

        return $value;
    }


    /**
     * Get code paths
     *
     * @return array<string, Dir>
     */
    public function getCodeDirs(): array
    {
        if (isset($this->config['codeDirs'])) {
            $dirs = $this->config['codeDirs'];
        } else {
            static $dirs = ['src', 'tests', 'stubs'];
        }

        $output = [];

        foreach ($dirs as $name) {
            $dir = $this->rootDir->getDir($name);

            if ($dir->exists()) {
                $output[(string)$name] = $dir;
            }
        }

        return $output;
    }

    /**
     * Get exports whitelist
     *
     * @return array<string>
     */
    public function getExportsWhitelist(): array
    {
        return $this->config['exports'] ?? [];
    }



    /**
     * Set config to save
     */
    public function setConfig(
        string $key,
        mixed $value
    ): void {
        /** @phpstan-var TConfig $config */
        $config = $this->mergeConfig(
            $this->newConfig,
            $this->parseConfig([$key => $value])
        );

        $this->newConfig = $config;
    }

    /**
     * Save user config
     */
    protected function saveConfig(): void
    {
        if (empty($this->newConfig)) {
            return;
        }


        // Merge data
        $data = [];

        if ($this->userFile->exists()) {
            $json = json_decode($this->userFile->getContents(), true);
            $data = $this->parseConfig(Coercion::toArray($json));
        }

        $data = $this->mergeConfig($data, $this->newConfig);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


        // Save data
        $this->userFile->putContents($json);

        // Ensure .gitignore
        $gitFile = $this->rootDir->getFile('.gitignore');
        $gitIgnore = '';

        if ($gitFile->exists()) {
            $gitIgnore = $gitFile->getContents();

            if (preg_match('|effigy\.json|', $gitIgnore)) {
                return;
            }
        }

        $gitIgnore .= "\n" . 'effigy.json' . "\n";
        $gitFile->putContents($gitIgnore);
    }


    /**
     * Get composer config
     *
     * @return array<string, mixed>
     */
    public function getComposerConfig(): array
    {
        if (!isset($this->composerConfig)) {
            return $this->reloadComposerConfig();
        }

        return $this->composerConfig;
    }

    /**
     * Reload composer config
     *
     * @return array<string, mixed>
     */
    public function reloadComposerConfig(): array
    {
        if ($this->composerFile->exists()) {
            /** @var array<string, mixed> */
            $json = json_decode($this->composerFile->getContents(), true);
            $this->composerConfig = $json;
        } else {
            $this->composerConfig = [];
        }

        return $this->composerConfig;
    }

    /**
     * Is package installed
     */
    public function isInstalled(string $name): bool
    {
        $config = $this->getComposerConfig();

        return
            /** @phpstan-ignore-next-line */
            isset($config['require'][$name]) ||
            /** @phpstan-ignore-next-line */
            isset($config['require-dev'][$name]);
    }


    /**
     * Load composer config
     *
     * @phpstan-return TConfig
     */
    protected function loadConfig(): array
    {
        $json = $this->getComposerConfig();
        /** @phpstan-ignore-next-line */
        $output = $this->parseConfig(Coercion::toArray($json['extra']['effigy'] ?? []));

        /** @phpstan-ignore-next-line */
        $this->scripts = $this->parseComposerScripts($json['scripts'] ?? []);

        if ($this->userFile->exists()) {
            $json = json_decode($this->userFile->getContents(), true);
            $output = array_merge($output, $this->parseConfig(Coercion::toArray($json)));
        }

        return $output;
    }


    /**
     * Parse config
     *
     * @param array<string, mixed> $config
     * @phpstan-return TConfig
     */
    protected function parseConfig(array $config): array
    {
        $output = [];

        foreach ($config as $key => $value) {
            switch ($key) {
                // string
                case 'entry':
                case 'php':
                    if (null !== ($value = Coercion::toStringOrNull($value))) {
                        $output[$key] = $value;
                    }
                    break;

                    // array<string, string>
                case 'params':
                    if (null !== ($value = Coercion::toArrayOrNull($value))) {
                        $output[$key] = [];

                        foreach ($value as $slug => $param) {
                            $output[$key][Coercion::forceString($slug)] = Coercion::forceString($param);
                        }
                    }
                    break;

                    // array<string>
                case 'codeDirs':
                case 'exports':
                    if (null !== ($value = Coercion::toArrayOrNull($value))) {
                        $output[$key] = [];

                        foreach ($value as $param) {
                            /** @phpstan-ignore-next-line */
                            $output[$key][] = Coercion::forceString($param);
                        }
                    }
                    break;
            }
        }

        /** @phpstan-var TConfig */
        return $output;
    }


    /**
     * Merge config data
     *
     * @param array<string, mixed> $config,
     * @param array<string, mixed> $newConfig
     * @return array<string, mixed>
     */
    protected function mergeConfig(
        array $config,
        array $newConfig
    ): array {
        foreach ($newConfig as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->mergeConfig(
                    Coercion::toArray($config[$key] ?? []),
                    $value
                );
            } else {
                $config[$key] = $value;
            }
        }

        return $config;
    }


    /**
     * Export for dump inspection
     */
    public function glitchDump(): iterable
    {
        yield 'properties' => [
            '*local' => $this->local,
            '*config' => $this->config,
            '*newConfig' => $this->newConfig,
            '*scripts' => $this->scripts,
            '*entryFile' => $this->entryFile,
            'runDir' => $this->runDir,
            'rootDir' => $this->rootDir,
            'composerFile' => $this->composerFile->exists() ? $this->composerFile : null,
            'userFile' => $this->userFile->exists() ? $this->userFile : null
        ];
    }
}
