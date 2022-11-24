<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Dictum;
use DecodeLabs\Exceptional;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class Template
{
    /**
     * @phpstan-var array<string, ?string>
     */
    protected array $slots = [];

    protected File $templateFile;

    public function __construct(
        string|File $templateFile
    ) {
        $this->templateFile = Atlas::file($templateFile);

        if (!$this->templateFile->exists()) {
            throw Exceptional::Runtime('Template file could not be found');
        }
    }


    /**
     * Set slots
     *
     * @param array<string, string> $slots
     * @return $this
     */
    public function setSlots(array $slots): static
    {
        foreach ($slots as $name => $slot) {
            $this->setSlot($name, $slot);
        }

        return $this;
    }

    /**
     * Get slots
     *
     * @return array<string, ?string>
     */
    public function getSlots(): array
    {
        return $this->slots;
    }

    /**
     * Set slot
     *
     * @return $this;
     */
    public function setSlot(
        string $name,
        string $slot
    ): static {
        $this->slots[$name] = $slot;
        return $this;
    }

    /**
     * Get slot
     */
    public function getSlot(string $name): ?string
    {
        if (array_key_exists($name, $this->slots)) {
            return $this->slots[$name];
        }

        return $this->slots[$name] = $this->generateSlot($name);
    }

    protected function generateSlot(string $name): ?string
    {
        $manifest = Integra::getLocalManifest();

        switch ($name) {
            case 'pkgName':
                return $manifest->getName() ??
                    Cli::ask('What is your full package name?', function () {
                        $name = Integra::$rootDir->getName();
                        return Integra::$rootDir->getParent()?->getName() . '/' . $name;
                    });

            case 'pkgTitle':
                $parts = explode('/', (string)$this->getSlot('pkgName'));
                return Dictum::name(array_pop($parts));

            case 'pkgDescription':
                return $manifest->getDescription() ??
                    Cli::ask('Describe your package');

            case 'pkgType':
                return $manifest->getType() ??
                    Cli::ask('What type of package is this?', 'library');

            case 'pkgLicense':
                $license = $manifest->getLicense();

                if (is_array($license)) {
                    if (empty($license)) {
                        $license = null;
                    } else {
                        return (string)json_encode($license);
                    }
                }

                return $license ??
                    Cli::ask('What license does your package use?', 'MIT');

            case 'pkgIntro':
                return $this->getPackageIntro();

            case 'date':
                return date('Y-m-d');

            case 'phpExtensions':
                return implode(', ', $this->getPackagePhpExtensions());

            case 'gitBranch':
                return $this->getGitBranch();

            case '__effigyVersion':
                if ($this->getSlot('pkgName') === 'decodelabs/effigy') {
                    return ' dev-develop';
                } else {
                    return '';
                }
        }

        return null;
    }


    /**
     * Interpolate and save to file
     */
    public function saveTo(
        string|File $file
    ): File {
        $content = (string)preg_replace_callback('/{{ ?([a-zA-Z0-9_]+) ?}}/', function ($matches) {
            $name = $matches[1];
            $output = $this->getSlot($name);

            if ($output === null) {
                $output = $matches[0];
            }

            return $output;
        }, $this->templateFile->getContents());

        $content = (string)preg_replace('/^\#\!(.*)\n/m', '', $content);

        $file = Atlas::file($file);
        $file->putContents($content);

        return $file;
    }


    /**
     * Get package intro
     */
    protected function getPackageIntro(): string
    {
        $output = $this->getSlot('pkgTitle') . ' provides ...';
        $name = (string)$this->getSlot('pkgName');

        if (substr($name, 0, 11) === 'decodelabs/') {
            $output .= "\n\n" . '_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._';
        }

        return $output;
    }


    /**
     * Get package PHP extensions
     *
     * @return array<string>
     */
    protected function getPackagePhpExtensions(): array
    {
        return array_unique(array_merge(
            ['intl'],
            Integra::getLocalManifest()->getRequiredExtensions()
        ));
    }

    /**
     * Get git branch
     */
    protected function getGitBranch(): string
    {
        return (string)exec('git branch --show-current');
    }
}
