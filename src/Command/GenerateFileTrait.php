<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Command;

use DecodeLabs\Atlas\File;
use DecodeLabs\Effigy\Controller;
use DecodeLabs\Effigy\Template;
use DecodeLabs\Terminus as Cli;

trait GenerateFileTrait
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    public function execute(): bool
    {
        Cli::getCommandDefinition()
            ->addArgument('-check|c', 'Check if file exists');

        $target = $this->getTargetFile();

        if (
            $target->exists() &&
            (
                !Cli::getArgument('check') ||
                !Cli::confirm($target->getName() . ' exists - overwrite?')
            )
        ) {
            Cli::operative($target->getName() . ' exists, skipping');
            return true;
        }

        $template = $this->getTemplate();
        $template->saveTo($target);
        Cli::success($target->getName() . ' created');

        if (!$this->afterFileSave($target)) {
            return false;
        }

        return true;
    }

    abstract protected function getTargetFile(): File;
    abstract protected function getTemplate(): Template;

    protected function afterFileSave(File $file): bool
    {
        return true;
    }
}
