<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;

class Lint implements Task
{
    public function execute(): bool
    {
        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);
        return Effigy::run('composer', 'global', 'exec', 'parallel-lint', ...$paths);
    }
}
