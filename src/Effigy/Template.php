<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Atlas\File;
use DecodeLabs\Dictum;
use DecodeLabs\Effigy;
use DecodeLabs\Hatch\FileTemplate;
use DecodeLabs\Terminus\Session;

class Template extends FileTemplate
{
    public function __construct(
        string|File $file,
        protected Effigy $effigy,
        protected Session $io,
    ) {
        parent::__construct($file);
    }

    protected function generateSlot(
        string $name
    ): ?string {
        $manifest = $this->effigy->project->getLocalManifest();

        switch ($name) {
            case 'pkgName':
                return $manifest->getName() ??
                    $this->io->ask('What is your full package name?', function () {
                        $name = $this->effigy->project->rootDir->name;
                        return $this->effigy->project->rootDir->getParent()?->name . '/' . $name;
                    });

            case 'pkgTitle':
                $parts = explode('/', (string)$this->getSlot('pkgName'));
                return Dictum::name(array_pop($parts));

            case 'pkgDescription':
                return $manifest->getDescription() ??
                    $this->io->ask('Describe your package');

            case 'pkgType':
                return $manifest->getType() ??
                    $this->io->ask('What type of package is this?', 'library');

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
                    $this->io->ask('What license does your package use?', 'MIT');

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


    protected function getPackageIntro(): string
    {
        return $this->getSlot('pkgTitle') . ' provides ...';
    }


    /**
     * @return array<string>
     */
    protected function getPackagePhpExtensions(): array
    {
        return array_unique(array_merge(
            ['intl'],
            $this->effigy->project->getLocalManifest()->getRequiredExtensions()
        ));
    }

    protected function getGitBranch(): string
    {
        return (string)exec('git branch --show-current');
    }
}
