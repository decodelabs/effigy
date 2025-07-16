<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Chronicle\ChangeLog\Block\Buffered\NextRelease;
use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Commandment\Action;
use DecodeLabs\Commandment\Argument;
use DecodeLabs\Commandment\Request;
use DecodeLabs\Effigy;
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'version',
    description: 'Version to release'
)]
class Release implements Action
{
    use ChangelogRendererTrait;
    use RepoAuthTrait;
    use RepoBranchTrait;
    use RepoVersionTrait;

    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request
    ): bool {
        if (!Effigy::run('prep')) {
            return false;
        }

        $repo = new Repository(
            Effigy::$project->rootDir
        );


        // Uncommitted changes
        if (!$this->checkUncommittedChanges($repo)) {
            return false;
        }


        // Branch setup
        if (!$branchSetup = $this->getBranchSetup($repo)) {
            return false;
        }

        [
            'develop' => $developBranch,
            'release' => $releaseBranch
        ] = $branchSetup;

        if (!$this->checkBranchSetup(
            $repo,
            $developBranch,
            $releaseBranch
        )) {
            return false;
        }


        // Load changelog
        $doc = $repo->parseChangeLog();
        $this->askGithubToken();


        // Parse version
        $version = $this->askRepoVersion(
            $doc,
            $request->parameters->tryString('version')
        );


        // Validate version
        if (!$this->checkVersion($repo, $version)) {
            return false;
        }


        $doc->generateNextRelease(
            version: $version,
            repository: $repo
        );

        $renderer = $this->getChangelogRenderer();

        if (!($release = $doc->getLastRelease()) instanceof NextRelease) {
            throw Exceptional::Runtime(
                'Failed to get next release from document'
            );
        }

        $releaseNotes = $renderer->renderNextRelease(
            $release,
            withHeader: true
        );

        $this->io->newLine();
        $this->io->info('Release notes:');
        $this->io->newLine();
        $this->io->{'.brightYellow'}($releaseNotes);
        $this->io->newLine();

        if (!$this->io->confirm('Is this release correct?')) {
            return false;
        }

        if (!Effigy::run('update-dev-version', $release->version)) {
            return false;
        }

        $doc->save();

        $this->io->newLine();
        $this->io->info('Committing changes...');

        if (!Effigy::runGit('add', '.')) {
            throw Exceptional::Runtime(
                'Failed to add files to git'
            );
        }

        if (!Effigy::runGit('commit', '-m', 'Updated changelog for release ' . $version)) {
            throw Exceptional::Runtime(
                'Failed to commit changelog'
            );
        }

        $this->io->newLine();
        $this->io->info('Merging to release branch...');

        if (!Effigy::runGit('checkout', $releaseBranch)) {
            throw Exceptional::Runtime(
                'Failed to checkout ' . $releaseBranch
            );
        }

        if (!Effigy::runGit('merge', $developBranch)) {
            throw Exceptional::Runtime(
                'Failed to merge ' . $developBranch . ' into ' . $releaseBranch
            );
        }

        $this->io->newLine();
        $this->io->info('Pushing changes to release branch...');

        $body = $renderer->renderNextRelease(
            $release,
            withHeader: false
        );

        if (!Effigy::runGit('tag', $version, '-m', $body)) {
            throw Exceptional::Runtime(
                'Failed to tag release ' . $version
            );
        }

        if (!Effigy::runGit('push', '--all', '--follow-tags')) {
            throw Exceptional::Runtime(
                'Failed to push ' . $releaseBranch
            );
        }

        if (!Effigy::runGit('checkout', $developBranch)) {
            throw Exceptional::Runtime(
                'Failed to checkout ' . $developBranch
            );
        }


        $this->io->newLine();
        $this->io->info('Publishing release...');

        $repo->publishNextRelease(
            release: $release,
            renderer: $renderer,
        );

        $this->io->newLine();
        $this->io->success('Release ' . $version . ' created and pushed to ' . $releaseBranch);
        $this->io->newLine();

        return true;
    }


    private function checkUncommittedChanges(
        Repository $repo
    ): bool {
        if ($repo->hasUncommittedChanges()) {
            $this->io->newLine();
            $this->io->error('You have uncommitted changes. Please commit or stash them before continuing.');
            $this->io->newLine();

            return false;
        }

        return true;
    }

    private function checkVersion(
        Repository $repo,
        string $version
    ): bool {
        $tags = $repo->getTags();

        if (in_array($version, $tags)) {
            $this->io->newLine();
            $this->io->error('Version ' . $version . ' tag already exists. Please specify a different version.');
            $this->io->newLine();
            return false;
        }

        return true;
    }
}
