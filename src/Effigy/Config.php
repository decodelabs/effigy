<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use Closure;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Integra\Project;

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
    protected const UserFilename = 'effigy.json';

    protected Project $project;
    protected File $file;

    /**
     * @var TConfig
     */
    protected array $data;

    /**
     * @var TConfig
     */
    protected array $new = [];

    public function __construct(
        Project $project
    ) {
        $this->project = $project;
        $this->file = $project->rootDir->getFile(self::UserFilename);
        $this->data = $this->loadData();
    }

    /**
     * @return TConfig
     */
    protected function loadData(): array
    {
        $output = $this->parse($this->project->getExtra()->effigy);

        if ($this->file->exists()) {
            /** @var array<string,mixed> */
            $json = Coercion::asArray(json_decode($this->file->getContents(), true));
            $output = array_merge($output, $this->parse($json));
        }

        return $output;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return TConfig
     */
    public function toArray(): array
    {
        return $this->data;
    }


    public function set(
        string $key,
        mixed $value
    ): void {
        /** @var TConfig $config */
        $config = $this->merge(
            $this->new,
            $this->parse([$key => $value])
        );

        $this->new = $config;
    }


    /**
     * @param string|Closure(Config):string $default
     */
    public function getParam(
        string $slug,
        string|Closure $default
    ): string {
        if (isset($this->data['params'][$slug])) {
            return $this->data['params'][$slug];
        }

        if ($default instanceof Closure) {
            $default = $default($this);
        }

        $this->new['params'][$slug] = $default;
        return $default;
    }


    public function getPhpBinary(): ?string
    {
        return $this->data['php'] ?? null;
    }


    public function hasEntry(): bool
    {
        return isset($this->data['entry']);
    }

    public function getEntry(): ?string
    {
        return $this->data['entry'] ?? null;
    }


    /**
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
            $dir = $this->project->rootDir->getDir($name);

            if ($dir->exists()) {
                $output[(string)$name] = $dir;
            }
        }

        return $output;
    }

    /**
     * @return array<string>
     */
    public function getIgnoredBins(): array
    {
        return $this->data['ignoreBins'] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getExportsWhitelist(): array
    {
        return $this->data['exports'] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getExecutablesWhitelist(): array
    {
        return $this->data['executables'] ?? [];
    }

    /**
     * @return array<string,string>
     */
    public function getLocalRepos(): array
    {
        return $this->data['localRepos'] ?? [];
    }





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
        $gitFile = $this->project->rootDir->getFile('.gitignore');
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
     * @param array<string,mixed>|Tree<string|int|float|bool> $config
     * @return TConfig
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
