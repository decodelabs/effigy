<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action\GeneratePhpstanConfig;

use DecodeLabs\Effigy;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Terminus\Session;

class PhpstanTemplate extends Template
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io
    ) {
        parent::__construct(
            __DIR__ . '/phpstan.template',
            $effigy,
            $io
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
        $dirs = $this->effigy->getCodeDirs();

        if (empty($dirs)) {
            return '';
        }

        $paths = [];

        foreach ($dirs as $name => $dir) {
            $paths[] = '- ' . $name;
        }

        return implode("\n        ", $paths);
    }
}
