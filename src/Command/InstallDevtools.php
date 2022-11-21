<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Effigy\Command;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Terminus as Cli;

class InstallDevtools implements Command
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        $packages = [
            'phpunit/phpunit' => '^9',
            'phpstan/phpstan' => '^1.8',
            'phpstan/extension-installer' => '^1.0',
            'php-parallel-lint/php-parallel-lint' => '^1.3',
            'symplify/easy-coding-standard' => '^11',
            'decodelabs/phpstan-decodelabs' => '^0.6'
        ];

        Cli::getCommandDefinition()
            ->addArgument('-global|g', 'Force global install');

        Cli::prepareArguments();

        if (!$this->controller->run(
            'composer', 'global', 'config', 'allow-plugins.phpstan/extension-installer', 'true'
        )) {
            return false;
        }

        if ($this->controller->isLocal()) {
            $args = ['composer', 'require', '--dev'];
        } else {
            $args = ['composer', 'global', 'require'];
        }

        foreach ($packages as $package => $version) {
            $args[] = '"' . $package . ':' . $version . '"';
        }

        return $this->controller->run(...$args);
    }
}
