<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Clip\Action\GenerateFileTrait as BaseGenerateFileTrait;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus\Session;

trait GenerateFileTrait
{
    use BaseGenerateFileTrait {
        __construct as private __generateFileTraitConstruct;
    }

    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
        protected Request $request
    ) {
        $this->__generateFileTraitConstruct($io, $request);
    }
}
