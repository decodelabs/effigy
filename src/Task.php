<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Clip\Task as TaskInterface;

interface Task extends TaskInterface
{
    public function __construct(
        Controller $controller
    );
}
