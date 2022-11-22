<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Archetype;
use DecodeLabs\Clip\Controller as ControllerInterface;
use DecodeLabs\Clip\Hub as ClipHub;
use DecodeLabs\Clip\Task as TaskInterface;
use DecodeLabs\Effigy;
use DecodeLabs\Veneer;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        Archetype::extend(TaskInterface::class, Task::class);

        $controller = new Controller(
            $this->appDir,
            $this->runDir,
            $this->composerFile
        );

        $this->context->container->bindShared(ControllerInterface::class, $controller);
        $this->context->container->bindShared(Controller::class, $controller);

        /** @phpstan-ignore-next-line */
        Veneer::register(Controller::class, Effigy::class);
    }
}
