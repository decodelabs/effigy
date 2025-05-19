<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;

class Lint implements Action
{
    public function execute(
        Request $request
    ): bool {
        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);

        if (Effigy::isLocal()) {
            return Effigy::$project->runBin('parallel-lint', ...$paths);
        } else {
            return Effigy::$project->runGlobalBin('parallel-lint', ...$paths);
        }
    }
}
