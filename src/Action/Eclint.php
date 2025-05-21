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
use DecodeLabs\Effigy\Template;
use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class Eclint implements Action
{
    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        if (!$this->ensureInstalled()) {
            throw Exceptional::ComponentUnavailable(
                message: 'eclint is not installed'
            );
        }

        $dirs = Effigy::getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        $command = 'check';
        $paths = array_map(
            fn ($dir) => $dir->path,
            $dirs
        );

        $output = Systemic::run([
            'eclint', $command, ...$paths
        ]);


        if ($output) {
            $this->io->success('No linting issues found');
        }

        return $output;
    }

    protected function ensureInstalled(): bool
    {
        if (Systemic::$os->which('eclint') === 'eclint') {
            return false;
        }

        $confFile = Effigy::$project->rootDir->getFile('.editorconfig');

        if (!$confFile->exists()) {
            $template = new Template(
                __DIR__ . '/GenerateEditorConfig/editorconfig.template'
            );

            $template->saveTo($confFile);
        }

        return true;
    }
}
