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
use DecodeLabs\Terminus\Session;

class CheckNonAscii implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $dirs = $this->effigy->getCodeDirs();

        if (empty($dirs)) {
            return true;
        }

        chdir($this->effigy->project->rootDir->path);

        $pathString = implode(' ', array_keys($dirs));
        $command = "! LC_ALL=C.UTF-8 find $pathString -type f -name \"*.php\" -not -name \"*.html.php\" -not -name \"*.htm.php\" -print0 | xargs -0 -- grep -PHn \"[^ -~]\" | grep -v '// @ignore-non-ascii$'";
        $output = exec($command);

        if ($output !== '') {
            $this->io->error('Non ascii:');
            $this->io->write((string)$output);
            $this->io->newLine();
        } else {
            $this->io->success('No non-ascii characters causing issues');
            $this->io->newLine();
        }

        chdir($this->effigy->project->rootDir->path);

        return $output === '';
    }
}
