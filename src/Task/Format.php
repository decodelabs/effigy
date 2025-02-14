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
            throw Exceptional::Runtime(
                message: 'Unable to find or create an ecs.php config'
            );
        }

        Cli::$command
            ->addArgument('-check|c', 'Check standards only');

        $args = ['ecs'];

        if (!Cli::$command['check']) {
            $args[] = '--fix';
        }

        if (Effigy::isCiMode()) {
            $args[] = '--no-progress-bar';
        }

        return Integra::runGlobalBin(...$args);
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
