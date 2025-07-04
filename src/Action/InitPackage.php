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
    public function execute(
        Request $request
    ): bool {
        // Init repo
        if (!Effigy::run('init-repo')) {
            return false;
        }

        // Composer.json
        if (!Effigy::run('generate-composer-config', '--check')) {
            return false;
        }

        // Make src folder
        Effigy::$project->rootDir->getDir('src')->ensureExists();

        // Editor config
        if (!Effigy::run('generate-editor-config', '--check')) {
            return false;
        }

        // Git attributes
        if (!Effigy::run('generate-gitattributes', '--check')) {
            return false;
        }

        // Git ignore
        if (!Effigy::run('generate-gitignore', '--check')) {
            return false;
        }

        // Changelog
        if (!Effigy::run('generate-changelog', '--check')) {
            return false;
        }

        // ECS
        if (!Effigy::run('generate-ecs-config', '--check')) {
            return false;
        }

        // Phpstan
        if (!Effigy::run('generate-phpstan-config', '--check')) {
            return false;
        }

        // Readme
        if (!Effigy::run('generate-readme', '--check')) {
            return false;
        }

        // CI
        if (!Effigy::run('generate-github-workflow')) {
            return false;
        }

        return true;
    }
}
