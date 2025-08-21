<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Action;

use DecodeLabs\Chronicle\Repository;
use DecodeLabs\Coercion;
use DecodeLabs\Effigy;

trait RepoBranchTrait
{
    protected Effigy $effigy;

    /**
     * @return array{develop: string, release: string}
     */
    private function getBranchSetup(
        Repository $repo
    ): ?array {
        $config = $repo->loadGitConfig();

        if (isset($config['gitflow branch'])) {
            return [
                'develop' => Coercion::toString($config['gitflow branch']['develop'] ?? 'develop'),
                'release' => Coercion::toString($config['gitflow branch']['master'] ?? 'main')
            ];
        }

        if (isset($config['effigy branch'])) {
            return [
                'develop' => Coercion::toString($config['effigy branch']['develop'] ?? 'develop'),
                'release' => Coercion::toString($config['effigy branch']['release'] ?? 'main')
            ];
        }

        if (!$develop = $this->io->ask(
            'What is the name of your develop branch?',
            'develop'
        )) {
            $this->io->newLine();
            $this->io->error('You must specify a develop branch name.');
            $this->io->newLine();
            return null;
        }

        if (!$release = $this->io->ask(
            'What is the name of your release branch?',
            'main'
        )) {
            $this->io->newLine();
            $this->io->error('You must specify a release branch name.');
            $this->io->newLine();
            return null;
        }

        $this->effigy->runGit('config', '--local', 'effigy.branch.develop', $develop);
        $this->effigy->runGit('config', '--local', 'effigy.branch.release', $release);

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

        if (
            !isset($branches[$develop]) ||
            !$branches[$develop]
        ) {
            $this->io->newLine();
            $this->io->error('You are not on the ' . $develop . ' branch. Please checkout the ' . $develop . ' branch before continuing.');
            $this->io->newLine();
            return false;
        }

        if (!isset($branches[$release])) {
            $this->io->newLine();
            $this->io->warning('The ' . $release . ' branch does not exist.');

            if (!$this->io->confirm('Would you like to create it?')) {
                return false;
            }

            $this->effigy->runGit('branch', $release);
        }


        // $this->effigy->runGit('fetch');

        return true;
    }
}
