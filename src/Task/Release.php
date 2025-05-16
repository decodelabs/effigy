<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Atlas;
use DecodeLabs\Chronicle\ChangeLog\Block\Buffered\NextRelease;
use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Chronicle\ChangeLog\Document;
use DecodeLabs\Chronicle\ChangeLog\Renderer\Generic as GenericRenderer;
use DecodeLabs\Clip\Task;
use DecodeLabs\Coercion;
use DecodeLabs\Dovetail;
use DecodeLabs\Effigy;
use DecodeLabs\Exceptional;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus as Cli;
use Dotenv\Dotenv;
use Github\Api\Repo;

class Release implements Task
{
    use RepoAuthTrait;
    use RepoVersionTrait;

    public function execute(): bool
    {
        Cli::$command
            ->addArgument('?version', 'Version to release');

        if(!Effigy::run('prep')) {
            return false;
        }

        $repo = new Repository(
            Effigy::$project->rootDir
        );


        // Uncommitted changes
        if(!$this->checkUncommittedChanges($repo)) {
            return false;
        }


        // Branch setup
        if(!$branchSetup = $this->getBranchSetup($repo)) {
            return false;
        }

        [
            'develop' => $developBranch,
            'release' => $releaseBranch
        ] = $branchSetup;

        if(!$this->checkBranchSetup(
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
            Coercion::tryString(
                Cli::$command['version']
            )
        );


        // Validate version
        if(!$this->checkVersion($repo, $version)) {
            return false;
        }


        $doc->generateNextRelease(
            version: $version,
            repository: $repo
        );

        $renderer = new GenericRenderer();

        if(!($release = $doc->getLastRelease()) instanceof NextRelease) {
            throw Exceptional::Runtime(
                'Failed to get next release from document'
            );
        }

        $body = $renderer->renderNextRelease(
            $release,
            withHeader: false
        );

        Cli::newLine();
        Cli::info('Release notes:');
        Cli::newLine();
        Cli::{'.brightYellow'}($body);
        Cli::newLine();

        if(!Cli::confirm('Is this release correct?')) {
            return false;
        }

        $doc->save();

        Cli::newLine();
        Cli::info('Committing changes...');

        if(!Effigy::runGit('add', '.')) {
            throw Exceptional::Runtime(
                'Failed to add files to git'
            );
        }

        if(!Effigy::runGit('commit', '-m', 'Updated changelog for release ' . $version)) {
            throw Exceptional::Runtime(
                'Failed to commit changelog'
            );
        }

        Cli::newLine();
        Cli::info('Merging to release branch...');

        if(!Effigy::runGit('checkout', $releaseBranch)) {
            throw Exceptional::Runtime(
                'Failed to checkout ' . $releaseBranch
            );
        }

        if(!Effigy::runGit('merge', $developBranch)) {
            throw Exceptional::Runtime(
                'Failed to merge ' . $developBranch . ' into ' . $releaseBranch
            );
        }

        Cli::newLine();
        Cli::info('Pushing changes to release branch...');

        if(!Effigy::runGit('tag', $version, '-m', $body)) {
            throw Exceptional::Runtime(
                'Failed to tag release ' . $version
            );
        }

        if(!Effigy::runGit('push', '--all', '--follow-tags')) {
            throw Exceptional::Runtime(
                'Failed to push ' . $releaseBranch
            );
        }

        if(!Effigy::runGit('checkout', $developBranch)) {
            throw Exceptional::Runtime(
                'Failed to checkout ' . $developBranch
            );
        }


        Cli::newLine();
        Cli::info('Publishing release...');

        $repo->publishNextRelease(
            release: $release,
            renderer: $renderer,
        );

        Cli::newLine();
        Cli::success('Release ' . $version . ' created and pushed to ' . $releaseBranch);
        Cli::newLine();

        return true;
    }


    private function checkUncommittedChanges(
        Repository $repo
    ): bool {
        if($repo->hasUncommittedChanges()) {
            Cli::newLine();
            Cli::error('You have uncommitted changes. Please commit or stash them before continuing.');
            Cli::newLine();

            return false;
        }

        return true;
    }



    /**
     * @return array{develop: string, release: string}
     */
    private function getBranchSetup(
        Repository $repo
    ): ?array {
        $config = $repo->loadGitConfig();

        if(isset($config['gitflow branch'])) {
            return [
                'develop' => Coercion::toString($config['gitflow branch']['develop'] ?? 'develop'),
                'release' => Coercion::toString($config['gitflow branch']['master'] ?? 'main')
            ];
        }

        if(isset($config['effigy branch'])) {
            return [
                'develop' => Coercion::toString($config['effigy branch']['develop'] ?? 'develop'),
                'release' => Coercion::toString($config['effigy branch']['release'] ?? 'main')
            ];
        }

        if(!$develop = Cli::ask(
            'What is the name of your develop branch?',
            'develop'
        )) {
            Cli::newLine();
            Cli::error('You must specify a develop branch name.');
            Cli::newLine();
            return null;
        }

        if(!$release = Cli::ask(
            'What is the name of your release branch?',
            'main'
        )) {
            Cli::newLine();
            Cli::error('You must specify a release branch name.');
            Cli::newLine();
            return null;
        }

        Effigy::runGit('config', '--local', 'effigy.branch.develop', $develop);
        Effigy::runGit('config', '--local', 'effigy.branch.release', $release);

        $repo->reloadGitConfig();

        return [
            'develop' => $develop,
            'release' => $release
        ];
    }

    private function checkBranchSetup(
        Repository $repo,
        string $develop,
        string $release
    ): bool {
        $branches = $repo->getBranches();

        if(
            !isset($branches[$develop]) ||
            !$branches[$develop]
        ) {
            Cli::newLine();
            Cli::error('You are not on the ' . $develop . ' branch. Please checkout the ' . $develop . ' branch before continuing.');
            Cli::newLine();
            return false;
        }

        if(!isset($branches[$release])) {
            Cli::newLine();
            Cli::warning('The ' . $release . ' branch does not exist.');

            if(!Cli::confirm('Would you like to create it?')) {
                return false;
            }

            Effigy::runGit('branch', $release);
        }


        //Effigy::runGit('fetch');

        return true;
    }


    private function checkVersion(
        Repository $repo,
        string $version
    ): bool {
        $tags = $repo->getTags();

        if(in_array($version, $tags)) {
            Cli::newLine();
            Cli::error('Version ' . $version . ' tag already exists. Please specify a different version.');
            Cli::newLine();
            return false;
        }

        return true;
    }
}
