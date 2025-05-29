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
use DecodeLabs\Exceptional;
use DecodeLabs\Terminus\Session;

#[Argument\Value(
    name: 'version',
    description: 'Version override'
)]
class UpdateDevVersion implements Action
{
    use RepoBranchTrait;

    public function __construct(
        protected Session $io
    ) {
    }

    public function execute(
        Request $request,
    ): bool {
        $repo = new Repository(
            Effigy::$project->rootDir
        );

        $version = $request->parameters->tryString('version');

        if($version === null) {
            $doc = $repo->parseChangeLog();
            $version = $doc->getLastVersion();
        }

        if(!preg_match('/^v?(\d+)\.(\d+)(\.\d+(-\w+)?)?$/', (string)$version, $matches)) {
            throw Exceptional::InvalidArgument(
                'Invalid version format: ' . $version
            );
        }

        $version = $matches[1] . '.' . $matches[2] . '.x-dev';

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


        $currentVersion = Effigy::$project->getConfig('extra.branch-alias.dev-develop');

        if ($currentVersion === $version) {
            $this->io->newLine();
            $this->io->{'brightGreen'}('Dev version is already up to date: ');
            $this->io->{'.brightYellow'}($version);
            $this->io->newLine();
            return true;
        }

        $this->io->newLine();
        $this->io->{'brightMagenta'}('Updating dev version: ');
        $this->io->{'.brightYellow'}($version);
        $this->io->newLine();

        if(!Effigy::$project->setConfig('extra.branch-alias.dev-'.$developBranch, $version)) {
            return false;
        }

        return Effigy::$project->run('update', '--lock');
    }
}
