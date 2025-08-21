<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy;

use DecodeLabs\Clip\Hub as ClipHub;
use DecodeLabs\Commandment\Action as ActionInterface;

class Hub extends ClipHub
{
    public function initializePlatform(): void
    {
        parent::initializePlatform();

        // @phpstan-ignore-next-line
        $this->archetype->map(ActionInterface::class, Action::class);
    }
}
