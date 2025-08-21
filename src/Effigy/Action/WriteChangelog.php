<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'version',
    description: 'Version to write'
)]
#[Argument\Flag(
    name: 'rewrite',
    shortcut: 'r',
    description: 'Rewrite the changelog'
)]
class WriteChangelog implements Action
{
    use ChangelogRendererTrait;
    use RepoAuthTrait;
    use RepoVersionTrait;

    public function __construct(
        protected Effigy $effigy,
        protected Session $io,
        protected Systemic $systemic,
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        $repo = new Repository(
            $this->effigy->project->rootDir,
            $this->systemic
        );

        $rewrite = $request->parameters->asBool('rewrite');

        $doc = $repo->parseChangeLog(
            rewrite: $rewrite
        );

        if (!$rewrite) {
            $this->askGithubToken();

            $version = $this->askRepoVersion(
                $doc,
                $request->parameters->tryString('version')
            );

            $doc->generateNextRelease(
                version: $version,
                repository: $repo
            );
        }

        $doc->save(
            $this->getChangelogRenderer()
        );

        $this->io->newLine();
        $this->io->success('Changelog written');
        $this->io->newLine();

        return true;
    }
}
