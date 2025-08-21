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
    public function __construct(
        protected Effigy $effigy,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $dirs = $this->effigy->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);

        if ($this->effigy->local) {
            return $this->effigy->project->runBin('parallel-lint', ...$paths);
        } else {
            return $this->effigy->project->runGlobalBin('parallel-lint', ...$paths);
        }
    }
}
