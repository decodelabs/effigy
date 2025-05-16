<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Effigy;
use DecodeLabs\Terminus as Cli;

class WriteChangelog implements Task
{
    use RepoAuthTrait;
    use RepoVersionTrait;

    public function execute(): bool
    {
        Cli::$command
            ->addArgument('?version', 'Version to write')
            ->addArgument('-rewrite|r', 'Rewrite the changelog');

        $repo = new Repository(
            Effigy::$project->rootDir
        );

        $rewrite = Coercion::toBool(
            Cli::$command['rewrite']
        );

        $doc = $repo->parseChangeLog(
            rewrite: $rewrite
        );

        if(!$rewrite) {
            $this->askGithubToken();

            $version = $this->askRepoVersion(
                $doc,
                Coercion::tryString(
                    Cli::$command['version']
                )
            );

            $doc->generateNextRelease(
                version: $version,
                repository: $repo
            );
        }

        $doc->save();

        Cli::newLine();
        Cli::success('Changelog written');
        Cli::newLine();

        return true;
    }
}
