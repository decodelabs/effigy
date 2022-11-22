<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus as Cli;

class Composer implements Task
{
    public function execute(): bool
    {
        /** @var array<string> */
        $args = array_values(Cli::getRequest()->getArguments());

        if (
            Effigy::isLocal() &&
            $args[0] === 'global'
        ) {
            array_shift($args);
        }

        return Effigy::newComposerLauncher($args)
            ->launch()
            ->wasSuccessful();
    }
}
