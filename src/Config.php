<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

/**
 * @phpstan-type TConfig array{
 *     'php'?: string,
 *     'entry'?: string,
 *     'params'?: array<string, string>,
 *     'codeDirs'?: array<string>,
 *     'ignoreBins'?: array<string>,
 *     'exports'?: array<string>,
 *     'executables'?: array<string>,
 *     'localRepos'?: array<string, string>
 * }
 */
class Config
{
    protected File $file;

    /**
     * @phpstan-var TConfig
     */
    protected array $data;

    /**
     * @phpstan-var TConfig
     */
    protected array $new = [];

    public function __construct(
        File $file
    ) {
        $this->file = $file;
        $this->data = $this->loadData();
    }

    /**
     * Load composer config
     *
     * @phpstan-return TConfig
     */
    protected function loadData(): array
    {
        $output = $this->parse(Integra::getExtra()->effigy);

        if ($this->file->exists()) {
            /** @var array<string,mixed> */
            $json = Coercion::asArray(json_decode($this->file->getContents(), true));
            $output = array_merge($output, $this->parse($json));
        }

        return $output;
    }

    /**
     * Get file
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Convert to array
     *
     * @phpstan-return TConfig
     */
    public function toArray(): array
    {
        return $this->data;
    }


    /**
     * Set value
     */
    public function set(
        string $key,
        mixed $value
    ): void {
        /** @phpstan-var TConfig $config */
        $config = $this->merge(
            $this->new,
            $this->parse([$key => $value])
        );

        $this->new = $config;
    }


    /**
     * Get param from config or user
     */
    public function getParam(
        string $slug
    ): string {
        if (isset($this->data['params'][$slug])) {
            return $this->data['params'][$slug];
        }

        $value = (string)Cli::ask(
            message: 'What is your "' . $slug . '" value?',
            validator: function (string $value) {
                return strlen($value) > 0;
            }
        );

        $this->new['params'][$slug] = $value;
        return $value;
    }


    /**
     * Get PHP binary
     */
    public function getPhpBinary(): ?string
    {
        return $this->data['php'] ?? null;
    }


    /**
     * Has entry
     */
    public function hasEntry(): bool
    {
        return isset($this->data['entry']);
    }

    /**
     * Get entry
     */
    public function getEntry(): ?string
    {
        return $this->data['entry'] ?? null;
    }


    /**
     * Get code paths
     *
     * @return array<string,Dir>
     */
    public function getCodeDirs(): array
    {
        if (isset($this->data['codeDirs'])) {
            $dirs = $this->data['codeDirs'];
        } else {
            $dirs = ['src', 'tests', 'stubs'];
        }

        $output = [];

        foreach ($dirs as $name) {
            $dir = Integra::$rootDir->getDir($name);

            if ($dir->exists()) {
                $output[(string)$name] = $dir;
            }
        }

        return $output;
    }

    /**
     * Get ignored bins
     *
     * @return array<string>
     */
    public function getIgnoredBins(): array
    {
        return $this->data['ignoreBins'] ?? [];
    }

    /**
     * Get exports whitelist
     *
     * @return array<string>
     */
    public function getExportsWhitelist(): array
    {
        return $this->data['exports'] ?? [];
    }

    /**
     * Get executables whitelist
     *
     * @return array<string>
     */
    public function getExecutablesWhitelist(): array
    {
        return $this->data['executables'] ?? [];
    }

    /**
     * Get local repos list
     *
     * @return array<string, string>
     */
    public function getLocalRepos(): array
    {
        return $this->data['localRepos'] ?? [];
    }





    /**
     * Save user config
     */
    public function save(): void
    {
        if (empty($this->new)) {
            return;
        }


        // Merge data
        $data = [];

        if ($this->file->exists()) {
            /** @var array<string,mixed> */
            $json = Coercion::asArray(json_decode($this->file->getContents(), true));
            $data = $this->parse($json);
        }

        $data = $this->merge($data, $this->new);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


        // Save data
        $this->file->putContents($json);

        // Ensure .gitignore
        $gitFile = Integra::$rootDir->getFile('.gitignore');
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
     * Parse config
     *
     * @param array<string,mixed>|Tree<string|int|float|bool> $config
     * @phpstan-return TConfig
     */
    public static function parse(
        array|Tree $config
    ): array {
        if ($config instanceof Tree) {
            $config = $config->toArray();
        }

        $output = [];

        foreach ($config as $key => $value) {
            switch ($key) {
                // string
                case 'entry':
                case 'php':
                    if (null !== ($value = Coercion::tryString($value))) {
                        $output[$key] = $value;
                    }
                    break;

                    // array<string, string>
                case 'params':
                case 'localRepos':
                    if (null !== ($value = Coercion::tryArray($value))) {
                        $output[$key] = [];

                        foreach ($value as $slug => $param) {
                            /** @phpstan-ignore-next-line */
                            $output[$key][Coercion::toString($slug)] = Coercion::toString($param);
                        }
                    }
                    break;

                    // array<string>
                case 'codeDirs':
                case 'ignoreBins':
                case 'exports':
                case 'executables':
                    if (null !== ($value = Coercion::tryArray($value))) {
                        $output[$key] = [];

                        foreach ($value as $param) {
                            /** @phpstan-ignore-next-line */
                            $output[$key][] = Coercion::toString($param);
                        }
                    }
                    break;
            }
        }

        /** @phpstan-ignore-next-line */
        return $output;
    }

    /**
     * Merge config data
     *
     * @param array<string,mixed> $config,
     * @param array<string,mixed> $new
     * @return array<string,mixed>
     */
    public static function merge(
        array $config,
        array $new
    ): array {
        foreach ($new as $key => $value) {
            if (is_array($value)) {
                /** @var array<string,mixed> */
                $data = Coercion::asArray($config[$key] ?? []);
                /** @var array<string,mixed> $value */
                $config[$key] = self::merge($data, $value);
            } else {
                $config[$key] = $value;
            }
        }

        return $config;
    }
}
