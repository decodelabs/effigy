<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Command\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus as Cli;

class Format implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::Runtime('Unable to find or create an ecs.php config');
        }

        Cli::getCommandDefinition()
            ->addArgument('-check|c', 'Check standards only')
            ->addArgument('-headless|h', 'No interaction mode');

        Cli::prepareArguments();


        $args = ['composer', 'global', 'exec', 'ecs'];
        $composerArgs = ['--'];

        if (Cli::getArgument('headless')) {
            $args[] = '--no-interaction';
        }

        if (!Cli::getArgument('check')) {
            $composerArgs[] = '--fix';
        }

        if (Cli::getArgument('headless')) {
            $composerArgs[] = '--no-progress-bar';
        }

        return $this->controller->run(...$args, ...$composerArgs);
    }

    protected function ensureInstalled(): bool
    {
        // Dependencies
        $pkgDir = $this->controller->rootDir->getDir('vendor/symplify/easy-coding-standard');

        if (!$pkgDir->exists()) {
            $this->controller->run('install-devtools');
        }

        // ECS file
        $ecsFile = $this->controller->rootDir->getFile('ecs.php');

        if (!$ecsFile->exists()) {
            $template = new EcsTemplate($this->controller);
            $template->saveTo($ecsFile);
        }

        return true;
    }
}
