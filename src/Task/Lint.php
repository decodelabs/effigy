<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Clip\Task;
use DecodeLabs\Effigy;
use DecodeLabs\Integra;

class Lint implements Task
{
    public function execute(): bool
    {
        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);
        return Integra::runGlobalBin('parallel-lint', ...$paths);
    }
}
