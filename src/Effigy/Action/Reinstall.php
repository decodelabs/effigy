<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;

#[Argument\Flag(
    name: 'update',
    shortcut: 'u',
    description: 'Update dependencies'
)]
class Reinstall implements Action
{
    public function __construct(
        protected Effigy $effigy,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $vendorDir = $this->effigy->project->rootDir->getDir('vendor');
        $vendorDir->delete();

        $command = $request->parameters->asBool('update') ? 'update' : 'install';
        return $this->effigy->project->run($command);
    }
}
