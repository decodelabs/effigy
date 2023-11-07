<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Dictum;
use DecodeLabs\Genesis\FileTemplate;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class Template extends FileTemplate
{
    protected function generateSlot(
        string $name
    ): ?string {
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

        return parent::generateSlot($name);
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
