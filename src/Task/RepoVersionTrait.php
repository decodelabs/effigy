<?php

/**
 * @package Effigy
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Effigy\Task;

use DecodeLabs\Chronicle\ChangeLog\Document;
use DecodeLabs\Terminus as Cli;

trait RepoVersionTrait
{
    private function askRepoVersion(
        Document $doc,
        ?string $version = null
    ): string {
        $origVersion = $version;

        while(true) {
            if(null !== ($lastVersion = $doc->getLastVersion())) {
                Cli::newLine();
                Cli::write('Last version: ');
                Cli::{'.brightRed'}($lastVersion);
            } else {
                Cli::info('This is the first release');
                Cli::write('New version: ');
                Cli::{'.brightGreen'}('v0.1.0');
            }

            if(
                $version !== null &&
                $doc->hasVersion($version)
            ) {
                Cli::warning('Version ' . $version . ' already exists');
                $version = null;
            }


            if($version !== null) {
                return $version;
            }

            if(!$lastVersion) {
                return 'v0.1.0';
            }

            $breaking = Cli::confirm('Does this release have any breaking changes?');

            $version = $doc->validateNextVersion(
                $breaking ? 'breaking' : 'feature'
            );

            Cli::newLine();
            Cli::newLine();
            Cli::write('New version: ');
            Cli::{$breaking ? '.brightYellow' : '.brightGreen'}($version);

            $confirm = Cli::confirm(
                'Is this version correct? ' . $version,
                true
            );

            if(!$confirm) {
                $version = $origVersion;
                continue;
            }

            break;
        }

        return $version;
    }
}
