<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use Decodelabs\Systemic;
use DecodeLabs\Terminus as Cli;

class CheckExecutablePermissions implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $exclude = [
            './.git/*',
            './vendor/*',
            './effigy'
        ];

        $exStr = [];

        foreach ($exclude as $glob) {
            $exStr[] = '-not -path \'' . $glob . '\'';
        }

        $result = Systemic::$process->launch(
            'find . -type f ' . implode(' ', $exStr) . ' -executable',
            [],
            $this->controller->rootDir
        );

        if (!$result->wasSuccessful()) {
            return false;
        }

        $paths = explode("\n", trim((string)$result->getOutput()));
        $config = $this->controller->getComposerConfig();

        /** @var array<string> $bins */
        $bins = $config['bin'] ?? [];
        $output = [];

        foreach ($paths as $path) {
            $path = substr($path, 2);

            if (!in_array($path, $bins)) {
                $output[] = $path;
            }
        }

        if (!empty($output)) {
            Cli::error('Unexpected executable file(s):');

            foreach ($output as $path) {
                Cli::write(' - ');
                Cli::{'.red'}($path);
            }

            return false;
        }

        return true;
    }
}
