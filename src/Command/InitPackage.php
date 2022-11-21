<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;

class InitPackage implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        // Composer.json
        if (!$this->controller->run('generate-composer-config', '--check')) {
            return false;
        }

        // Editor config
        if (!$this->controller->run('generate-editor-config', '--check')) {
            return false;
        }

        // Git attributes
        if (!$this->controller->run('generate-gitattributes', '--check')) {
            return false;
        }

        // Git ignore
        if (!$this->controller->run('generate-gitignore', '--check')) {
            return false;
        }

        // Changelog
        if (!$this->controller->run('generate-changelog', '--check')) {
            return false;
        }

        // ECS
        if (!$this->controller->run('generate-ecs-config', '--check')) {
            return false;
        }

        // Phpstan
        if (!$this->controller->run('generate-phpstan-config', '--check')) {
            return false;
        }

        // Readme
        if (!$this->controller->run('generate-readme', '--check')) {
            return false;
        }

        return true;
    }
}
