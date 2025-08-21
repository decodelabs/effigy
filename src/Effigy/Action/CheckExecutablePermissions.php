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
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

class CheckExecutablePermissions implements Action
{
    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
        protected Systemic $systemic,
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        $exclude = [
            './.git/*',
            './vendor/*',
            './effigy',
            '*/node_modules/*',
        ];

        $exStr = [];

        foreach ($exclude as $glob) {
            $exStr[] = '-not -path \'' . $glob . '\'';
        }

        $result = $this->systemic->capture(
            'find . -type f \\( -perm -u=x -o -perm -g=x -o -perm -o=x \\) ' . implode(' ', $exStr) . ' -exec test -x {} \\; -print',
            $this->effigy->project->rootDir->path
        );

        if (!$result->wasSuccessful()) {
            $this->io->error('Unable to capture executable file list');
            return false;
        }

        $result = trim((string)$result->getOutput());

        if ($result === '') {
            $this->io->success('No executable files found');
            return true;
        }

        $paths = explode("\n", $result);
        $bins = $this->effigy->project->getLocalManifest()->getBinFiles();
        $whitelist = $this->effigy->getExecutablesWhitelist();
        $output = [];

        foreach ($paths as $path) {
            $path = substr($path, 2);

            if (
                !in_array($path, $bins) &&
                !in_array($path, $whitelist)
            ) {
                $output[] = $path;
            }
        }

        if (!empty($output)) {
            $this->io->error('Unexpected executable file(s):');

            foreach ($output as $path) {
                $this->io->write(' - ');
                $this->io->{'.red'}($path);
            }

            return false;
        }

        $this->io->success('All executable files are expected');
        return true;
    }
}
