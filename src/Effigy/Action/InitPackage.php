<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;

class InitPackage implements Action
{
    public function __construct(
        protected Effigy $effigy,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        // Init repo
        if (!$this->effigy->run('init-repo')) {
            return false;
        }

        // Composer.json
        if (!$this->effigy->run('generate-composer-config', '--check')) {
            return false;
        }

        // Make src folder
        $this->effigy->project->rootDir->getDir('src')->ensureExists();

        // Editor config
        if (!$this->effigy->run('generate-editor-config', '--check')) {
            return false;
        }

        // Git attributes
        if (!$this->effigy->run('generate-gitattributes', '--check')) {
            return false;
        }

        // Git ignore
        if (!$this->effigy->run('generate-gitignore', '--check')) {
            return false;
        }

        // Changelog
        if (!$this->effigy->run('generate-changelog', '--check')) {
            return false;
        }

        // ECS
        if (!$this->effigy->run('generate-ecs-config', '--check')) {
            return false;
        }

        // Phpstan
        if (!$this->effigy->run('generate-phpstan-config', '--check')) {
            return false;
        }

        // Readme
        if (!$this->effigy->run('generate-readme', '--check')) {
            return false;
        }

        // CI
        if (!$this->effigy->run('generate-github-workflow')) {
            return false;
        }

        return true;
    }
}
