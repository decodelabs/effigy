<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action\GenerateEcsConfig;

use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;

class EcsTemplate extends Template
{
    public function __construct()
    {
        parent::__construct(
            __DIR__ . '/ecs.template'
        );
    }

    protected function generateSlot(
        string $name
    ): ?string {
        switch ($name) {
            case 'paths': return $this->generatePathString();
        }

        return parent::generateSlot($name);
    }

    protected function generatePathString(): string
    {
        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return '[]';
        }

        $paths = [];

        foreach ($dirs as $name => $dir) {
            $paths[] = '__DIR__.\'/' . $name . '\'';
        }

        return '[' . implode(', ', $paths) . ']';
    }
}
