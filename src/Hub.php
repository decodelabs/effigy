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
use DecodeLabs\Glitch;
use DecodeLabs\Monarch;
use DecodeLabs\Pandora\Container;
use DecodeLabs\Terminus;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();

        Archetype::map(TaskInterface::class, Task::class);

        $controller = new Controller();

        if(Monarch::$container instanceof Container) {
            Monarch::$container->bindShared(ControllerInterface::class, $controller);
            Monarch::$container->bindShared(Controller::class, $controller);
        }

        set_exception_handler(function ($e) use ($controller) {
            if ($controller->isLocal()) {
                Glitch::handleException($e);
            } else {
                Terminus::newLine();
                Terminus::error($e->getMessage());
                Terminus::newLine();
            }
        });
    }
}
