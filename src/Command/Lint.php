<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Exceptional;

class Lint implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        if (!$this->ensureInstalled()) {
            throw Exceptional::ComponentUnavailable('Unable to use php-parallel-lint/php-parallel-lint');
        }

        $dirs = $this->controller->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $paths = array_keys($dirs);
        return $this->controller->run('composer', 'exec', 'parallel-lint', ...$paths);
    }

    protected function ensureInstalled(): bool
    {
        // Dependencies
        $pkgDir = $this->controller->rootDir->getDir('vendor/php-parallel-lint/php-parallel-lint');

        if (!$pkgDir->exists()) {
            $this->controller->run('composer', 'require', 'php-parallel-lint/php-parallel-lint', '--dev');
        }

        return true;
    }
}
