<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Integra;
use DecodeLabs\Terminus as Cli;

class Version implements Task
{
    public function execute(): bool
    {
        return Integra::runGlobal('show', 'decodelabs/effigy', '|grep \'versions :\'');
    }
}
