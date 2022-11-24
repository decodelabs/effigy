<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Task\GenerateEcsConfig\EcsTemplate;
use DecodeLabs\Exceptional;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class Format implements Task
{
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

        return Effigy::run(...$args, ...$composerArgs);
    }

    protected function ensureInstalled(): bool
    {
        // ECS file
        $ecsFile = Integra::$rootDir->getFile('ecs.php');

        if (!$ecsFile->exists()) {
            $template = new EcsTemplate();
            $template->saveTo($ecsFile);
        }

        return true;
    }
}
