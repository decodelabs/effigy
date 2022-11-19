<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

interface Command
{
    public function __construct(Controller $controller);
    public function execute(): bool;
}
